<?php

namespace App\Http\Controllers;

use App\Services\ChatroomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MainController extends Controller {
    public function connectToChatroom(Request $request): JsonResponse {
        Log::debug('MainController::connectToChatroom called');
        // Log::debug($request->collect());
        $validated = $request->validate([
            // 'link' => 'required|url',
            // 'format' => 'required|string|in:mp3,m4a,flac,wav,aac,alac,opus,vorbis',
            // 'quality' => 'required|integer|min:0|max:10',
            'userName' => 'required|string|in:Alice,Bob',
            'publicKey' => 'required|array',
        ]);
        // $jsonResponseData = ChatroomService::connectUser(
        //     $validated['userName'],
        //     $validated['publicKey'],
        // );
        // return response()->json($jsonResponseData);
        return response()->json(ChatroomService::connectUser(
            $validated['userName'],
            $validated['publicKey'],
        ));
    }

    public function sendMessage(Request $request): JsonResponse {
        Log::debug('MainController::sendMessage called');
        $validated = $request->validate([
            // 'link' => 'required|url',
            // 'format' => 'required|string|in:mp3,m4a,flac,wav,aac,alac,opus,vorbis',
            // 'quality' => 'required|integer|min:0|max:10',
            'from' => 'required|string|in:Alice,Bob',
            'to' => 'required|string|in:Alice,Bob',
            'message' => 'required|string',
        ]);
        return response()->json(ChatroomService::handleSendMessage(
            $validated['from'],
            $validated['to'],
            $validated['message'],
        ));
    }
}
