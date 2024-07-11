<?php

namespace App\Http\Controllers\Backend\AI;

use App\Models\Project;
use App\Models\Language;
use App\Models\ProjectLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;
use App\Models\SubscriptionPackage;
use App\Http\Controllers\Controller;

class AiWriterController extends Controller
{
    public function __construct()
    {
        if (getSetting('enable_ai_rewriter') == '0') {
            flash(localize('AI Writer is not available'))->info();
            redirect()->route('writebot.dashboard')->send();
        }
    }
    public function index()
    {
        $user = auth()->user();
        if ($user->user_type == "customer") {
            $package = optional(activePackageHistory())->subscriptionPackage ?? new SubscriptionPackage;
            if ($package->allow_ai_rewriter == 0) {
                abort(403);
            }
        } else {
            if (!auth()->user()->can('ai_rewriter')) {
                abort(403);
            }
        }
        $languages = Language::isActiveForTemplate()->latest()->get();
        return view('backend.pages.aiWriter.index-ai-writer', [
            'languages' => $languages
        ]);
    }
    # generate contents
    public function generate(Request $request)
    {
        $user = auth()->user();

        # 2. verify if user has access to the template [template available in subscription package]
        if ($user->user_type == "customer") {
            // check package balance
            $checkBalanceData = activePackageBalance();
            if (!empty($checkBalanceData)) {
                return $checkBalanceData;
            }
            // check word limit  
            if (availableDataCheck('words') <= 10) {
                $data = [
                    'status'  => 400,
                    'success' => false,
                    'message' => localize('Your word balance is low, please upgrade you plan'),
                ];
                return $data;
            }
        }

        # 4. generate prompt in selected language 
        $max_tokens     = getSetting('default_max_result_length', -1);

        if ($request->max_tokens != null) {
            $max_tokens     = (int)$request->max_tokens;
        }
        $inputAll = $request->all();
        $inputAll['max_tokens'] = $max_tokens;

        $prompt = strip_tags($request->about) . ' in ' . $request->lang . ' language ' . strip_tags($request->about);
        if ($request->max_tokens != -1) {
            $prompt .= ' .The tone of voice should be ' . $request->tone . ' and the output must be completed in ' . $request->max_tokens . ' words. Do not generate translation.';
        } else {
            $prompt .= ' .The tone of voice should be ' . $request->tone . '. Do not generate translation.';
        }
        if (preg_match("/bad_words_found/i", $prompt) == 1) {
            $badWords =  explode('_#themeTags', rtrim($prompt, ","));
            $data = [
                'status'  => 400,
                'success' => false,
                'message' => localize('Please remove these words from your inputs') . '-' . $badWords[1],
            ];
            return $data;
        }

        # 5. apply openAi model based on admin configuration  
        $model = getSetting('default_open_ai_model') ?? 'gpt-3.5-turbo'; // default model
        if ($user->user_type == "customer" && activePackageHistory() && activePackageHistory()->subscriptionPackage) {
            $model = activePackageHistory()->subscriptionPackage->openai_model->key;
        }

        # 6. generate contents
        $temperature    = (float)$request->creativity;

        // ai params
        $aiParams = [
            'model'             => $model,
            'temperature'       => $temperature,
            'presence_penalty'  => 0.6,
            'frequency_penalty' => 0,
            'stream'            => true
        ];

        if ($max_tokens != -1) {
            $aiParams['max_tokens'] = $max_tokens;
        }
        # opts
        $aiParams['messages'] = [[
            "role" => "user",
            "content" => $prompt
        ]];

        if ($request->project_id != null) {
            $project = Project::whereId($request->project_id)->first();
            $request->session()->put('project_id', $project->id);
        } else {
            $projectTitle = "Untitled Project - " . date("Y-m-d");
            $project = new Project;
            $project->user_id       = $user->id;
            $project->model_name    = $aiParams['model'];
            $project->title         = $projectTitle;
            $project->slug          = preg_replace('/\s+/', '-', trim($projectTitle)) . '-' . strtolower(Str::random(5));
            $project->content_type  = 'Ai ReWriter';
            $project->save();
            $request->session()->put('project_id', $project->id);
        }

        session()->put('aiParams', $aiParams);

        $data = [
            'status'            => 200,
            'success'           => true,
            'title'             => $project->title,
            'project_id'        => $project->id ?? ''
        ];
        return $data;
    }
    # processContents
    public function processContents()
    {
        $user            = auth()->user();
        $opts            = session('aiParams');
        $project_id      = session('project_id');
        $project         = Project::where('id', $project_id)->first();

        $promptsToken     = count(explode(' ', $opts['messages'][0]['content']));
        $project->prompts = $promptsToken;
        $project->input_prompt = $opts['messages'][0]['content'];

        # 1. init openAi
        $open_ai = new OpenAi(openAiKey());
        session()->put('project_id', $project_id);
        return response()->stream(function () use ($project, $open_ai, $user, $opts) {

            $text   = "";
            $output = "";


            $open_ai->chat($opts, function ($curl_info, $data) use (&$text, &$project, &$user) {
                $chatResponse = explode("data:", $data);
                if (!empty($chatResponse)) {
                    $output = "";
                    foreach ($chatResponse as $singleData) {
                        if (!empty($singleData)) {
                            $singleData = json_decode(trim($singleData), true);

                            if (isset($singleData["choices"][0]["delta"]["content"])) {
                                $content = $singleData["choices"][0]["delta"]["content"];
                                $text   .= $content;
                                $output .= $content;
                            }
                        }
                    }
                    $text = str_replace(["\r\n", "\r", "\n"], "<br>", $text);
                    $project->content = $text;

                    $completionToken     = count(explode(' ', $text));
                    $project->completion = $completionToken;
                    $project->words      = $project->prompts + $completionToken;
                    $project->save();
                }
                echo $data;
                echo "\n\n";
                echo PHP_EOL;

                if (ob_get_level() < 1) {
                    ob_start();
                }
                ob_flush();
                flush();
                return strlen($data);
            });


            $completionToken = count(explode(' ', $text));
            $tokens          = $project->prompts + $completionToken;

            $this->updateUserWords($tokens, $user);

            $latestPackage      = activePackageHistory();
            $previousBalance    = $latestPackage ? $latestPackage->this_month_available_words : null;
            $after_balance      = $latestPackage ? $latestPackage->this_month_available_words - $tokens : null;

            # keep log
            $logData                      =  [
                'user_id'                 => $project->user_id,
                'project_id'              => $project->id,
                'subscription_history_id' => $latestPackage ? $latestPackage->id : null,
                'subscription_package_id' => $latestPackage ? $latestPackage->subscription_package_id : null,
                'template_id'             => $project->template_id != null ? $project->template_id : null,
                'custom_template_id'      => $project->custom_template_id != null ? $project->custom_template_id : null,
                'model_name'              => $project->model_name,
                'content'                 => $output,
                'content_type'            => $project->content_type,
                'words'                   => $tokens,
                'prompt_words'            => $project->prompts,
                'completion_words'        => $completionToken,
                'previous_balance'        => $previousBalance,
                'after_balance'           => $after_balance
            ];
            $this->createLog($logData);
        }, 200, [
            'X-Accel-Buffering' => 'no',
            'Cache-Control'     => 'no-cache',
            'Content-Type'      => 'text/event-stream',
        ]);
    }
    # updateUserWords - take token as word
    public function updateUserWords($tokens, $user)
    {
        if ($user->user_type == "customer") {
            updateDataBalance('words', $tokens, $user);
        }
    }
    # keep log
    public function createLog($data)
    {
        ProjectLog::create($data);
    }
}
