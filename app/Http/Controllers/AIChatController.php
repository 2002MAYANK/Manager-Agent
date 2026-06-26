<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIChatService;
use Illuminate\Support\Facades\Session;

class AIChatController extends Controller
{
    protected $aiChatService;

    public function __construct(AIChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    public function send(Request $request)
    {
        $message = $request->input('message');
        $context = $request->input('context', 'Dashboard');

        $history = Session::get('ai_chat_history', []);
        
        $reply = $this->aiChatService->getResponse($message, $context, $history);

        $history[] = ['role' => 'user', 'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => $reply];
        Session::put('ai_chat_history', $history);

        return response()->json(['reply' => $reply]);
    }

    public function history()
    {
        $history = Session::get('ai_chat_history', []);
        return response()->json(['history' => $history]);
    }

    public function clear()
    {
        Session::forget('ai_chat_history');
        return response()->json(['status' => 'success']);
    }
}
