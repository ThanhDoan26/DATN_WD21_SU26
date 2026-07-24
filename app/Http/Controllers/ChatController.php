<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\ChatRequest;
use App\Services\AI\ChatbotService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    public function chat(ChatRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            $user = $request->user();
            $response = $this->chatbotService->chat($data['message'], $user);

            return response()->json([
                'success' => true,
                'message' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function chatWeb(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {
            $user = auth()->user();
            $response = $this->chatbotService->chat($request->input('message'), $user);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $response,
                ]);
            }

            return redirect()->back()->with('chat_open', true);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI đang bận, vui lòng thử lại sau.'
                ], 500);
            }

            return redirect()->back()->with('chat_open', true)->with('chat_error', 'AI đang bận, vui lòng thử lại sau.');
        }
    }
}
