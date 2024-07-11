<?php

namespace App\Http\Controllers\Backend\AI;

use App\Models\AiChat;
use App\Mail\EmailManager;
use App\Models\AiChatPrompt;
use Illuminate\Http\Request;
use App\Models\AiChatMessage;
use Orhanerday\OpenAi\OpenAi;
use App\Models\AiChatCategory;
use App\Models\AiChatPromptGroup;
use App\Services\WriteBotService;
use App\Models\SubscriptionPackage;
use App\Http\Controllers\Controller;
use App\Http\Services\SerperService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Notifications\EmailChatMessages;


class AiChatController extends Controller
{
    public function __construct()
    {
        if (getSetting('enable_ai_chat') == '0') {
            flash(localize('AI chat is not available'))->info();
            redirect()->route('writebot.dashboard')->send();
        }
    }

    # chat index
    public function index(Request $request, WriteBotService $writeBotService)
    {

        $searchKey = null;
        $user = user();
        if (isCustomer()) {
            $package = optional(activePackageHistory())->subscriptionPackage ?? new SubscriptionPackage;
            if ($package->allow_ai_chat == 0) {
                abort(403);
            }
        } else {
            if (!auth()->user()->can('ai_chat')) {
                abort(403);
            }
        }

        $chatExpertIds = [];
        $conditions = [['type', 'chat']];
        if (!isCustomer()) {
            $chatExpertIds = $writeBotService->getAiChatCategories(null, null, $conditions);
            $chatExperts   = $writeBotService->getAiChatCategories(true, 1, $conditions);
        } else {
            $chatExpertIds = $writeBotService->getAiChatCategories(null, 1, $conditions);
            $chatExperts   = $writeBotService->getAiChatCategories(true, 1, $conditions);
        }


        $chatListQuery = AiChat::orderBy('updated_at', 'DESC')->with('messages', 'category')->where('user_id', $user->id)->whereIn('ai_chat_category_id', $chatExpertIds);

        if (!empty($request->search)) {
            $chatListQuery = $chatListQuery->where('title', 'like', '%' . $request->search . '%');
            $searchKey = $request->search;
        }

        if (!empty($request->expert)) {
            $chatList     = $chatListQuery->where('ai_chat_category_id', $request->expert)->get();
        } else {
            $chatList     = $chatListQuery->where('ai_chat_category_id', 1)->get();
        }



        $promptGroups       = AiChatPromptGroup::oldest();
        $promptGroups       = $promptGroups->get();
        $prompts            = AiChatPrompt::latest()->get();

        $conversation = $chatListQuery->first();
        return view('backend.pages.aiChat.index', compact('chatExperts', 'chatList', 'conversation', 'searchKey', 'promptGroups', 'prompts'));
    }

    # new conversation
    public function store(Request $request)
    {
        $user = user();
        if (isCustomer()) {
            $package = optional(activePackageHistory())->subscriptionPackage ?? new SubscriptionPackage;
            if ($package->allow_ai_chat == 0) {
                $data = [
                    'status'  => 400,
                    'success' => false,
                    'message' => localize('AI Chat is not available in this package, please upgrade you plan'),
                ];
                return $data;
            }
        }
        $expert = AiChatCategory::query()->find($request->ai_chat_category_id);

        /* When Expert is empty response a error json */
        if(empty($expert)){
            return  [
                'status'                => 400,
                'ai_chat_category_id'   => $request->ai_chat_category_id,
                'success'               => false,
                'message'               => localize('Expert not found'),
            ];
        }

        $conversation                      = new AiChat;
        $conversation->user_id             = $user->id;
        $conversation->ai_chat_category_id = $request->ai_chat_category_id;
        $conversation->title               = $expert->name . localize(' Chat');
        $conversation->save();

        $message = new AiChatMessage;
        $message->ai_chat_id = $conversation->id;
        $message->user_id    = $user->id;
        if ($expert->role == 'default') {
            $result =  localize("Hello! I am $expert->name, and I'm here to answer your all questions.");
        } else {
            $result =  localize("Hello! I am $expert->name, and I'm $expert->role. $expert->assists_with.");
        }
        $message->response   = $result;
        $message->result   = $result;
        $message->save();

        $chatList = AiChat::latest();
        $chatList = $chatList->where('ai_chat_category_id', $expert->id)->where('user_id', $user->id)->get();

        $promptGroups       = AiChatPromptGroup::oldest();
        $promptGroups       = $promptGroups->get();
        $prompts            = AiChatPrompt::latest()->get();

        $data = [
            'status'                 => 200,
            'chatList'               => view('backend.pages.aiChat.inc.chat-list', compact('chatList'))->render(),
            'messagesContainer'      => view('backend.pages.aiChat.inc.messages-container', compact('conversation', 'promptGroups', 'prompts'))->render(),
        ];
        return $data;
    }

    # update conversation
    public function update(Request $request)
    {
        $conversation = AiChat::whereId((int) $request->chatId)->first();
        $conversation->title = $request->value;
        $conversation->save();
    }

    # delete conversation
    public function delete($id)
    {
        $conversation = AiChat::findOrFail((int)$id);
        AiChatMessage::where('ai_chat_id', $conversation->id)->delete();
        $conversation->delete();
        flash(localize('Chat has been deleted successfully'))->success();
        return back();
    }

    # new message
    public function newMessage(Request $request)
    {

        $chat = AiChat::where('id', (int) $request->chat_id)->first(); // TODO Required Existance checking
        $category = AiChatCategory::where('id', $request->category_id)->first();

        $user = auth()->user();

        // check word limit; need to have min 10 words balance
        if (isCustomer() && availableDataCheck('words') <= 10) {
            $data = [
                'status'                => 400,
                'ai_chat_category_id'   => $request->category_id,
                'success'               => false,
                'message'               => localize('Your word balance is low, please upgrade you plan'),
            ];

            return $data;
        }


        $prompt = $request->prompt; // TODO Required
        $total_used_tokens = 0;

        $message                = new AiChatMessage;
        $message->ai_chat_id    = $chat->id;
        $message->user_id       = $user->id;
        $message->prompt        = $prompt;
        $message->result        = $prompt;
        $message->save();

        $message->aiChat->touch(); // updated at

        $chat_id = $chat->id;
        $message_id = $message->id;

        $request->session()->put('chat_id', $chat_id);
        $request->session()->put('message_id', $message_id);
        $request->session()->put('category_id', $request->category_id);
        $request->session()->put('real_time_data', $request->real_time_data == 1 ? 1 :null);

        $data = [
            'status'              => 200,
            'ai_chat_category_id' => $request->category_id,
            'success'             => false,
            'message'             => '',
        ];
        return $data;
    }

    # ai response
    public function process()
    {
        
        $chat_id    = session('chat_id');
        $message_id = session('message_id');
        $realTime   = session('real_time_data');
        $message    = AiChatMessage::whereId((int)$message_id)->first();

        $chat                     = AiChat::whereId((int) $chat_id)->first();
        $lastThreeMessageQuery    = $chat->messages()->where('prompt', null)->latest()->take(4);
        $lastThreeMessage         = $lastThreeMessageQuery->get()->reverse();


        $expert = $chat->category;
        $expert->chat_training_data = str_replace(array("\r", "\n"), '', $expert->chat_training_data) ?? null; // TODO : Training like


        $prompt    = '';
        $newPrompt = [];

        if ($expert->chat_training_data != null) {
            $trainedData = json_decode(json_decode($expert->chat_training_data));
            foreach ($trainedData as $data) {
                $msg = [
                    "role"      => $data->role,
                    "content"   => $data->content,
                ];
                $history[] = $msg;
            }
            $prompt = "You will now play a character and respond as that character (You will never break character). Your name is $expert->short_name. I want you to act as a $expert->role. As a $expert->role please answer this, $message->prompt. Do not include your name, role in your answer.";
        } else {
            $prompt    = $message->prompt;
            $history[] = ["role" => "system", "content" => "You are a helpful assistant."];
        }

        $message->input_prompt = $prompt;
        $message->save();

        /**
         * Todo::Promt generating as like Sofliq -- always sent expert message & User prompt
         * */
        if (count($lastThreeMessage) > 1 && !$realTime) {
            foreach ($lastThreeMessage as $key => $threeMessage) {
                if ($key != 0) {
                    if ($threeMessage->prompt != null) {
                        $history[] = ["role" => "user", "content" => $threeMessage->prompt];
                    } else {
                        $history[] = ["role" => "assistant", "content" => $threeMessage->response];
                    }
                } else {
                    $newPrompt = ["role" => "user", "content" => $prompt];
                }
            }
        }
        elseif($realTime && getSetting('serper_api_key') !=null){

            $serper = new SerperService(getSetting('serper_api_key'));
            $question  = [
              'q'=> $message->prompt
            ];
            $search = $serper->search($question);
            $final_prompt =
            "Prompt: " . $message->prompt.
            '\n\nWeb search json results: '
            .json_encode($search).
            '\n\nInstructions: Based on the Prompt generate a proper response with help of Web search results(if the Web search results in the same context).Only if the prompt require links: (make curated list of links and descriptions using only the <a target="_blank">,write links with using <a target="_blank"> with mrgin Top of <a> tag is 5px and start order as number and write link first and then write description).Must not write links if its not necessary. Must not mention anything about the prompt text.';
            // unset($history);
            $newPrompt = ["role" => "user", "content" => $final_prompt];
        }
        else {
            $newPrompt = ["role" => "user", "content" => $prompt];
        }

        $history[] = $newPrompt;

        $model =  openAiModel('chat');

        # 1. init openAi
        $open_ai = new OpenAi(openAiKey());
        $user    = auth()->user();
        $opts    = [
            'model'             => $model,
            'messages'          => $history,
            'temperature'       => 1.0,
            'presence_penalty'  => 0.6,
            'frequency_penalty' => 0,
            'stream'            => true
        ];
        if(getSetting('max_tokens')){
            $opts["max_tokens"] = (int) getSetting('max_tokens');
        }

        $random_number = time();
        session()->put('random_number', $random_number);
        session()->save();

        return response()->stream(function () use ($chat_id, $open_ai, $user, $opts, $random_number, $prompt) {
            $text = "";
            $open_ai->chat($opts, function ($curl_info, $data) use (&$text, $chat_id, $user, $random_number) {

                if ($obj = json_decode($data) and $obj->error->message != "") {
                    echo (json_encode($obj->error->message));
                } else {
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
                    }


                    # Update credit balance
                    $words   = count(explode(' ', $text));
                    $output  = str_replace(["\r\n", "\r", "\n"], "<br>", $text);

                    $message = AiChatMessage::updateOrCreate([
                        'random_number' => $random_number
                    ], [
                        'ai_chat_id'    => $chat_id,
                        'user_id'       => $user->id,
                        'response'      => $text,
                        'result'        => $output,
                        'words'         => $words,
                    ]);
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

            $this->updateUserWords($completionToken, $user);
        }, 200, [
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
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
    # updateBalanceStopGeneration
    public function updateBalanceStopGeneration(Request $request)
    {
        $random_number = session()->get('random_number');
        $user = user();
        if ($random_number && isCustomer()) {
            $aiChatMessage = AiChatMessage::where('random_number', $random_number)->where('user_id', $user->id)->first();
            if ($aiChatMessage) {
                $words = $aiChatMessage->words;
                $this->updateUserWords($words, $user);
                session()->forget('random_number');
                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false]);
    }
    # get messages
    public function getMessages(Request $request)
    {
        $conversation = AiChat::whereId((int) $request->chatId)->first();
        if (is_null($conversation)) {
            $data = [
                'status' => 400
            ];
            return $data;
        }


        $promptGroups       = AiChatPromptGroup::oldest();
        $promptGroups       = $promptGroups->get();
        $prompts            = AiChatPrompt::latest()->get();

        $data = [
            'status'            => 200,
            'messagesContainer' => view('backend.pages.aiChat.inc.messages-container', compact('conversation', 'promptGroups', 'prompts'))->render(),
        ];
        return $data;
    }

    # get conversations
    public function getConversations(Request $request)
    {
        $conversationsQuery = AiChat::where('ai_chat_category_id', (int) $request->ai_chat_category_id)->where('user_id', auth()->user()->id)->latest('updated_at');

        $chatList = $conversationsQuery->get();
        $conversation = $conversationsQuery->first();


        $promptGroups       = AiChatPromptGroup::oldest();
        $promptGroups       = $promptGroups->get();
        $prompts            = AiChatPrompt::latest()->get();
        $ai_chat_category_id = $request->ai_chat_category_id;
        $data = [
            'status'                 => 200,
            'ai_chat_category_id'   => $ai_chat_category_id,
            'chatRight'      => view('backend.pages.aiChat.inc.chat-right', compact('conversation', 'chatList', 'conversation', 'promptGroups', 'prompts'))->render(),
        ];
        return $data;
    }

    # SEND IN EMAIL
    public function sendInEmail(Request $request)
    {
        if ($request->email == null) {
            flash(localize('Please type an email'))->error();
            return back();
        }

        $conversation = AiChat::findOrFail((int) $request->conversation_id);
        if (is_null($conversation)) {
            flash(localize('Chat not found'))->error();
            return back();
        }

        try {
            $array['view'] = 'emails.chat';
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['subject'] = $conversation->title;
            $array['conversation'] = $conversation;
            $array['messages'] = $conversation->messages;

            Mail::to($request->email)->queue(new EmailManager($array));
            flash(localize('Chat successfully sent to email'))->success();
        } catch (\Throwable $th) {
            flash($th->getMessage())->error();
        }
        return back();
    }
    // download, copy chat history
    public function downloadChatHistory(Request $request)
    {

        try {
            $basePath = public_path('/');
            $type = $request->type;
            $conversation = AiChat::whereId((int) $request->chatId)->with('messages')->first();
            $messages = null;
            $name   = $conversation->category ? $conversation->category->name : 'ai_chat';

            if ($conversation) {
                $messages  = $conversation->messages;
            }

            if (!$messages) {
                flash(localize('No Message Fund'));
                return redirect()->back();
            }
            $data = ['messages' => $messages, 'conversation' => $conversation, 'type' => $type];
            if ($type == 'html') {
                $name =  str_replace(' ', '_', $name) . '.html';
                $file_path = $basePath . $name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }

                $view = view('backend.pages.aiChat.download.AI_ChatBot', $data)->render();
                file_put_contents($file_path, $view);
                return response()->download($file_path);
            }
            if ($type == 'word') {
                $name =  str_replace(' ', '_', $name) . '.doc';
                $file_path = $basePath . $name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }

                $view = view('backend.pages.aiChat.download.AI_ChatBot', $data)->render();
                file_put_contents($file_path, $view);
                return response()->download($file_path);
            }
            if ($type == 'pdf') {
                return  view('backend.pages.aiChat.download.AI_ChatBot', $data);
            }

            if ($type == 'copyChat') {
                return  view('backend.pages.aiChat.download.copyChat', $data);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
