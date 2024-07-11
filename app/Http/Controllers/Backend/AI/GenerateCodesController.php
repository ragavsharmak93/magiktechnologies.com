<?php

namespace App\Http\Controllers\Backend\AI;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SubscriptionHistory;
use App\Models\SubscriptionPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;
use Str;

class GenerateCodesController extends Controller
{
    public function __construct()
    {
        if (getSetting('enable_ai_code') == '0') {
            redirect()->route('writebot.dashboard')->send();
        }
    }


    # code
    public function index()
    {
        $user = user();
        if (isCustomer()) {
            $package = optional(activePackageHistory())->subscriptionPackage ?? new SubscriptionPackage;
            if ($package->allow_ai_code == 0) {
                abort(403);
            }
        } else {
            if (!auth()->user()->can('generate_code')) {
                abort(403);
            }
        }

        return view('backend.pages.templates.generate-codes');
    }

    # generate code
    public function generate(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            $response = [
                'status'    => 400,
                'message'   => localize('In demo mode, this feature is disabled'),
                'success'   => false,
            ];
            return $response;
        }
        $user = auth()->user();

        # 1. init openAi
        $open_ai = new OpenAi(openAiKey());

        # 2. verify if user has access to the template [template available in subscription package]
        if ($user->user_type == "customer") {
            // check package balance
            $checkBalanceData = activePackageBalance('allow_ai_code');
            if (!empty($checkBalanceData)) {
                return $checkBalanceData;
            }
            // check word limit
            if (availableDataCheck('words') <= 0) {
                $data = [
                    'status'  => 400,
                    'success' => false,
                    'message' => localize('Your word balance is low, please upgrade'),
                ];
                return $data;
            }
        }
        # 4. generate code
        $model = openAiModel('code');
        $result = $open_ai->chat([
            'model' => $model,
            'messages' => [
                [
                    "role" => "system",
                    "content" => "You are a creative assistant that writes code."
                ],
                [
                    "role" => "user",
                    "content" => $request->description,
                ],
            ],
            'temperature' => 1,
            'max_tokens' => 4000,
        ]);


        # parse response
        $result = json_decode($result, true);

        $outputContents = '';
        if (isset($result['choices'])) {
            $outputContents = ($result['choices'][0]['message']['content']);
            $promptsToken = $result['usage']['prompt_tokens'];
            $completionToken = $result['usage']['completion_tokens'];
            $tokens = $result['usage']['total_tokens'];

            # 5. Save it as a project
            $projectTitle = $request->title;
            if ($request->project_id == null) {
                $project = new Project;
                $project->user_id       = $user->id;
                $project->model_name    = $model;
                $project->title         = $projectTitle;
                $project->slug          = preg_replace('/\s+/', '-', trim($projectTitle)) . '-' . strtolower(Str::random(5));
                $project->prompts       = $promptsToken;
                $project->completion    = $completionToken;
                $project->words         = $tokens;
                $project->content_type  = 'code';
                $project->content       = trim($outputContents);
                $project->save();
            } else {
                $project = Project::where('id', $request->project_id)->first();
                if (!is_null($project)) {
                    $project->words         = $tokens;
                    $project->content       = trim($outputContents);
                    $project->save();
                }
            }

            $latestPackage = activePackageHistory();
            $previousBalance = $latestPackage ?  $latestPackage->this_month_available_words : null;
            $after_balance = $latestPackage ?  $latestPackage->this_month_available_words - $project->words : null;
            # keep log
            $logData = [
                'user_id' => $project->user_id,
                'project_id' => $project->id,
                'subscription_history_id' => optional(activePackageHistory())->id,
                'subscription_package_id' => optional(activePackageHistory())->subscription_package_id,
                'model_name' => $project->model_name,
                'content' => $project->content,
                'content_type' => $project->content_type,
                'words' => $project->words,
                'prompt_words' => $promptsToken,
                'completion_words' => $completionToken,
                'previous_balance' => $previousBalance,
                'after_balance' => $after_balance
            ];

            $generateController = new GenerateContentsController();
            $generateController->createLog($logData);


            # 6. update word limit for user or admin/staff
            $this->updateUserWords($tokens, $user);

            $data = [
                'status'            => 200,
                'success'           => true,
                'output'            => view('backend.pages.templates.inc.contentCode', compact('project'))->render(),
                'title'             => $projectTitle,
                'project_id'        => $project->id ?? '',
                'usedPercentage'    => view('backend.pages.templates.inc.used-words-percentage')->render(),
            ];
            return $data;
        } else {
            if (isset($result['error']['message'])) {
                $message = $result['error']['message'];
            } else {
                $message = localize('There is an issue with the openai account');
            }
            $data = [
                'status'  => 400,
                'success' => false,
                'message' => $message
            ];
            return $data;
        }
        $data = [
            'status'  => 500,
            'success' => false,
        ];
        return $data;
    }

    # updateUserWords - take token as word
    public function updateUserWords($tokens, $user)
    {
        if ($user->user_type == "customer") {
            updateDataBalance('words', $tokens, $user);
        }
    }
}
