<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\ChatMessageRequest;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatbotService $chatbot,
    ) {}

    public function init(Request $request): JsonResponse
    {
        $sessionId = $request->string('session_id')->toString() ?: (string) Str::uuid();

        return response()->json($this->chatbot->init($sessionId));
    }

    public function message(ChatMessageRequest $request): JsonResponse
    {
        $sessionId = $request->validated('session_id') ?: (string) Str::uuid();

        return response()->json($this->chatbot->reply(
            message: $request->validated('message'),
            sessionId: $sessionId,
            user: $request->user(),
            ipAddress: $request->ip() ?: '0.0.0.0',
        ));
    }

    public function reset(Request $request): JsonResponse
    {
        $sessionId = $request->string('session_id')->toString() ?: (string) Str::uuid();

        return response()->json($this->chatbot->reset($sessionId));
    }
}
