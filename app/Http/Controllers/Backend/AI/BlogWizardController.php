<?php

namespace App\Http\Controllers\Backend\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiBlogWizardBlogPublishStoreRequest;
use App\Models\AiBlogWizard;
use App\Models\AiBlogWizardArticle;
use App\Models\AiBlogWizardArticleLog;
use App\Models\Blog;
use App\Models\Language;
use App\Models\SubscriptionPackage;
use App\Services\GenerateCapability\GenerateCapabilityService;
use App\Services\WriteBotService;
use App\Traits\PopulateWizardData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlogWizardController extends Controller
{
    use PopulateWizardData;

    public function __construct()
    {
        if (getSetting('enable_blog_wizard') == '0') {
            flash(localize('AI Blog Wizard is not available'))->info();
            redirect()->route('writebot.dashboard')->send();
        }
    }

    # blog wizard index
    public function index(Request $request, WriteBotService $writeBotService)
    {

        $user = user();
        if (isCustomer()) {
            $package = optional(activePackageHistory())->subscriptionPackage ?? new SubscriptionPackage;
            if ($package->allow_blog_wizard == 0) {
                abort(403);
            }
        } else {
            if (!auth()->user()->can('blog_wizard')) {
                abort(403);
            }
        }

        $blogs = AiBlogWizard::latest();
        $blogs = $blogs->where('user_id', userId());
        $blogs = $blogs->paginate(paginationNumber());

        $data["blogs"]          = $blogs;
        $data["blogCategories"] = $writeBotService->getBlogCategories();
        $data["tags"]           = $writeBotService->getTags();

        return view('backend.pages.blogWizard.index')->with($data);
    }

    # return view of create form
    public function create(Request $request)
    {
        if(!isCapable()) {
            return redirect()->route("blog.wizard");
        }

        $user = user();

        if (isCustomer()) {
            $package = optional(activePackageHistory())->subscriptionPackage ?? new SubscriptionPackage;
            if ($package->allow_blog_wizard == 0) {
                abort(403);
            }
        } else {
            if (!auth()->user()->can('blog_wizard')) {
                abort(403);
            }
        }
        $aiBlogWizard = null;
        if($request->uuid){
            $aiBlogWizard = AiBlogWizard::where('uuid', $request->uuid)->where('user_id', $user->id)->first();
        }

        $languages = Language::isActiveForTemplate()->latest()->get();
        return view('backend.pages.blogWizard.create',compact('languages', 'aiBlogWizard'));
    }

    # init article
    /**
     * When customer doesn't contain any balance. return a insufficient balance errors.
     * */
    public function initArticle(Request $request){

        if(!isCapable()) {
            return back();
        }

        try {
            $user                       = auth()->user();
            $checkOpenAiInstance        = $this->openAiInstance();

            if($checkOpenAiInstance['status']){
                $outlines       = collect($request->outlines)->implode(' ');
                $prompt         = "$request->title. $request->keywords. $outlines";

                $promptsToken    = count(explode(' ', $prompt));
                // filter bad words
                $parsePromptController = new ParsePromptsController;
                $foundBadWords = $parsePromptController->filterBadWords(["prompt" => $prompt]);
                if ($foundBadWords != '') {
                    $prompt = "bad_words_found_#themeTags" . $foundBadWords;
                    if (preg_match("/bad_words_found/i", $prompt) == 1) {
                        $badWords =  explode('_#themeTags', rtrim($prompt, ","));
                        $data = [
                            'status'  => 400,
                            'success' => false,
                            'message' => localize('Please remove these words from your inputs') . '-' . $badWords[1],
                        ];
                        return $data;
                    }
                }

                $aiBlogWizardArticle = AiBlogWizardArticle::where('ai_blog_wizard_id', $request->ai_blog_wizard_id)->first();

                if(is_null($aiBlogWizardArticle)){
                    $aiBlogWizardArticle = new AiBlogWizardArticle;
                    $aiBlogWizardArticle->ai_blog_wizard_id = $request->ai_blog_wizard_id;
                    $aiBlogWizardArticle->title             = $request->title;
                    $aiBlogWizardArticle->keywords          = $request->keywords;
                    $aiBlogWizardArticle->prompt_tokens     = $promptsToken;
                    $aiBlogWizardArticle->outlines          = json_encode($request->outlines);
                    $aiBlogWizardArticle->created_by        = $user->id;
                }else{
                    $aiBlogWizardArticle->title             = $request->title;
                    $aiBlogWizardArticle->keywords          = $request->keywords;
                    $aiBlogWizardArticle->prompt_tokens     = $promptsToken;
                    $aiBlogWizardArticle->outlines          = json_encode($request->outlines);
                    $aiBlogWizardArticle->num_of_copies     += 1;
                }
                $aiBlogWizardArticle->value = null;
                if($request->image != null){
                    $aiBlogWizardArticle->image             = $request->image;
                }
                $aiBlogWizardArticle->updated_by = null;

                if(isCustomer()){
                    try {
                        $activePackageHistory = $checkOpenAiInstance['activePackageHistory'];
                        $aiBlogWizard = $aiBlogWizardArticle->aiBlogWizard;
                        $aiBlogWizard->subscription_history_id = $activePackageHistory->id;
                        $aiBlogWizard->save();

                        $request->session()->put('subscription_history_id', $activePackageHistory->id);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                $aiBlogWizardArticle->save();

                $request->session()->put('ai_blog_wizard_article_id', $aiBlogWizardArticle->id);
                $request->session()->put('request_max_tokens', $request->max_tokens ?? null);
                $request->session()->put('outlines', json_encode($request->outlines));
                $request->session()->put('title', $request->title);
                $request->session()->put('keywords', $request->keywords);
                $request->session()->put('lang', $request->lang);
                #$request->session()->put('article_generate_max_word', setArticleGenMaxWord());
                $request->session()->put('article_generate_max_word', $request->article_generate_max_word);

                $data = [
                    'status'            => 200,
                    'success'           => true,
                    'message'           => '',
                    'articleId'         => $aiBlogWizardArticle->id,
                    'article'           => view('backend.pages.blogWizard.stepsData.article', ['article'=> $aiBlogWizardArticle])->render(),
                    'usedPercentage'    => view('backend.pages.templates.inc.used-words-percentage')->render(),
                ];
                return $data;
            }
            else{
                return $checkOpenAiInstance;
            }
        }
        catch (\Throwable $e){
            commonLog("Failed to Init Article", errorArray($e));

            return apiError($e->getMessage());
        }

    }

    # generate article
    public function generateArticle(){
        if(!isCapable()) {
            return back();
        }

        try {
            $aiBlogWizardArticle        = AiBlogWizardArticle::where('id', session('ai_blog_wizard_article_id'))->first();

            $user                       = auth()->user();
            $checkOpenAiInstance        = $this->openAiInstance();

            # ai prompt
            $promptOutlines     = session('outlines');
            $title              = session('title');
            $keywords           = session('keywords');
            $lang               = session('lang');
            $request_max_tokens = session()->get('request_max_tokens');
            $article_generate_max_word = getArticleGenMaxWord();

          //  $oldPrompt         = "This is the title: $title. These are the keywords: $keywords. This is the heading list: $promptOutlines. Expand each heading section to generate article in $lang language. Do not add other headings or write more than the specific headings. Give the heading output in bold font.";

            if(isCustomer()){
                $isAllowed        = (new GenerateCapabilityService())->checkGenerateCapability( $article_generate_max_word);
                if(!$isAllowed) {
                    return balanceError();
                }
            }

            $prompt = promptGenerator($lang,$title, $promptOutlines);

            $promptsToken    = count(explode(' ', $prompt));
            $aiBlogWizardArticle->prompt_tokens = $promptsToken;
            $model =  openAiModel('blog_wizard');
            // session forget every stream
            session()->forget('request_max_tokens');

            $opts = [
                'model'     => $model,
                'messages'  => [[
                    "role" => "user",
                    "content" => $prompt
                ]],
                'temperature' => 1.0,
                'presence_penalty' => 0.6,
                'frequency_penalty' => 0,
                'stream' => true
            ];
            if (getSetting('max_tokens')) {
                $opts["max_tokens"] = (int) getSetting('max_tokens');
            }
            // Max Token Assign
            ($article_generate_max_word > 0 ? $opts['max_tokens'] = (int) $article_generate_max_word : null);


            Log::info("Options for the Model : ".json_encode($opts));

            session()->put('ai_blog_wizard_article_id_for_balance', $aiBlogWizardArticle->id);
            session()->save();

            $lineNo = 0;
            # make api call to openAi
            return response()->stream(function () use ($checkOpenAiInstance, $opts, $user, $aiBlogWizardArticle, $prompt, $lineNo){
                # 1. init openAi
                $open_ai = $checkOpenAiInstance['open_ai'];
                $text = "";
                $open_ai->chat($opts, function ($curl_info, $data) use (&$text, &$aiBlogWizardArticle, & $user, &$lineNo) {
                    $chatResponse = explode("data:", $data);
                    if (!empty($chatResponse)) {
                        $output = "";
                        foreach ($chatResponse as $singleData) {
                            if (!empty($singleData)) {
                                $singleData = json_decode(trim($singleData), true);

                                if (isset($singleData["choices"][0]["delta"]["content"])) {
                                    $content = $singleData["choices"][0]["delta"]["content"];

//                                    $lineNo += 1;
//                                    Log::info("$lineNo New Streaming Data is : ".json_encode($content));

                                    $text   .= $content;
                                    $output .= $content;
                                }
                            }
                        }
                        $aiBlogWizardArticle->value         = $text;
                        $words                              = count(explode(' ', ($text))); // todo:: add user input counter
                        $aiBlogWizardArticle->total_words   = $words;
                        $aiBlogWizardArticle->save();
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
                $promptsToken    = count(explode(' ', $prompt));
                $tokens          = $promptsToken + $completionToken;

                if (isCustomer()) {
                    updateDataBalance('words', $completionToken, $user);
                }

                $aiBlogWizardArticle->save();

                $aiBlogWizard                 = $aiBlogWizardArticle->aiBlogWizard;
                $aiBlogWizard->completed_step = 5;
                $aiBlogWizard->total_words    += $completionToken;
                $aiBlogWizard->save();

                // log
                $aiBlogWizardArticleLog                            = new AiBlogWizardArticleLog;
                $aiBlogWizardArticleLog->user_id                   = $user->id;
                $aiBlogWizardArticleLog->ai_blog_wizard_id         = $aiBlogWizard->id;
                $aiBlogWizardArticleLog->ai_blog_wizard_article_id = $aiBlogWizardArticle->id;
                $aiBlogWizardArticleLog->subscription_history_id   = session('subscription_history_id');
                $aiBlogWizardArticleLog->total_words               = $aiBlogWizardArticle->total_words;
                $aiBlogWizardArticleLog->prompt_tokens             = $promptsToken;
                $aiBlogWizardArticleLog->save();

            }, 200, [
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'text/event-stream',
            ]);

        }
        catch (\Throwable $e){
            commonLog("Failed to Generate Article", errorArray($e));

            return [
                'status'  => false,
                'message' => $e->getMessage(),
                "code"    => 400
            ];

        }
    }
    # updateBalanceStopGeneration
    public function updateBalanceStopGeneration(Request $request)
    {
        $id = session()->get('ai_blog_wizard_article_id_for_balance');

        $user = auth()->user();
        if($id && $user->user_type == "customer") {
            $aiBlogWizardArticle = AiBlogWizardArticle::where('id', $id)->where('created_by', $user->id)->first();
            if($aiBlogWizardArticle){
                $words = $aiBlogWizardArticle->total_words;
                updateDataBalance('words', $words, $user);

                $aiBlogWizard                 = $aiBlogWizardArticle->aiBlogWizard;
                $aiBlogWizard->completed_step = 5;
                $aiBlogWizard->total_words    += $words;
                $aiBlogWizard->save();

                $aiBlogWizardArticleLog                            = new AiBlogWizardArticleLog;
                $aiBlogWizardArticleLog->user_id                   = $user->id;
                $aiBlogWizardArticleLog->ai_blog_wizard_id         = $aiBlogWizard->id;
                $aiBlogWizardArticleLog->ai_blog_wizard_article_id = $aiBlogWizardArticle->id;
                $aiBlogWizardArticleLog->subscription_history_id   = session('subscription_history_id');
                $aiBlogWizardArticleLog->total_words               = $aiBlogWizardArticle->total_words;
                $aiBlogWizardArticleLog->prompt_tokens             = $aiBlogWizardArticle->prompt_tokens;
                $aiBlogWizardArticleLog->save();

                session()->forget('ai_blog_wizard_article_id_for_balance');
                return response()->json(['success'=>true]);
            }
        }
        return response()->json(['success'=>false]);
    }
    # show
    public function show($uuid){
        $aiBlogWizard = AiBlogWizard::where('uuid', $uuid)->first();
        $article = $aiBlogWizard->aiBlogWizardArticle;
        return view('backend.pages.blogWizard.show',compact('article'));
    }

    # edit
    public function edit($uuid){
        $aiBlogWizard = AiBlogWizard::where('uuid', $uuid)->first();
        $article = $aiBlogWizard->aiBlogWizardArticle;
        return view('backend.pages.blogWizard.edit',compact('article'));
    }

    # update
    public function update(Request $request){
        $article = AiBlogWizardArticle::where('id', $request->ai_blog_wizard_article_id)->first();
        $article->title = $request->title;
        $article->value = $request->article;
        $article->updated_by = auth()->user()->id;
        $article->save();
        return [
            'status'    => 200,
            'success'   => true
        ];
    }


    # delete blog wizard
    public function delete($uuid){
        $aiBlogWizard = AiBlogWizard::where('uuid', $uuid)->first();
        if($aiBlogWizard->aiBlogWizardKeyword){
            $aiBlogWizard->aiBlogWizardKeyword->delete();
        }
        if($aiBlogWizard->aiBlogWizardTitle){
            $aiBlogWizard->aiBlogWizardTitle->delete();
        }

        $aiBlogWizard->aiBlogWizardImages()->delete();

        $aiBlogWizard->aiBlogWizardOutlines()->delete();

        if($aiBlogWizard->aiBlogWizardArticle){
            $aiBlogWizard->aiBlogWizardArticle->delete();
        }
        $aiBlogWizard->delete();
        flash(localize('Blog has been deleted successfully'))->success();
        return back();
    }
    # download blog
    public function downloadBlog(Request $request)
   {
    try {
        $basePath = public_path('/');
        $type = $request->type;

        $article = AiBlogWizardArticle::where('id', $request->id)->where('created_by', auth()->user()->id)->first();;
        if(!$article) {
            flash(localize('Blog not found for you'));
            return redirect()->back();
        }
        $data = ['title'=>$article->title, 'blog'=>$article->value, 'type'=>$type];

        if($type == 'html') {
            $name =  'blog' .'.html';
            $file_path = $basePath.$name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $view = view('backend.pages.blogWizard.download_blog', $data)->render();
            file_put_contents($file_path, $view);
            return response()->download($file_path);


        }
        if($type == 'word') {
            $name =  'blog' .'.doc';
            $file_path = $basePath.$name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            $view = view('backend.pages.blogWizard.download_blog', $data)->render();
            file_put_contents($file_path, $view);
            return response()->download($file_path);
        }
        if($type == 'pdf') {
            return  view('backend.pages.blogWizard.download_blog', $data);
        }

    } catch (\Throwable $th) {
        throw $th;
    }
   }


    public function publishToBlog(AiBlogWizardBlogPublishStoreRequest $request,$id, WriteBotService $writeBotService)
    {
        try {
            DB::beginTransaction();

            $aiBlogWizard = $writeBotService->getAiBlogWizardById($id);

            if($aiBlogWizard->is_blog_published == 1){
                flash("Ai Blog Wizard Article Already Published.")->error();
                return back();
            }

            if(!$aiBlogWizard->aiBlogWizardArticle){

                flash("Ai Blog Wizard Article Not Found.")->error();

                return back();
            }

            $writeBotService->publishToBlog($aiBlogWizard, $request->validated());

            $aiBlogWizard->update([
                "is_blog_published" => 1
            ]);

            flash("Ai Blog Wizard Article Published Successfully.")->success();

            DB::commit();

            return back();
        }
        catch (\Throwable $e){
            DB::rollBack();
            
        }

   }
}
