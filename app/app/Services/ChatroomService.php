<?php

namespace App\Services;

use App\Events\RelayMessage;
use App\Events\UserJoined;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChatroomService {
    public static function connectUser(string $userName, array $publicKey): array {
        Log::debug('ChatroomService::connectUser called');
        // update the user's public key with the one received
        // $user = User::where('name', $userName)->firstOrFail();
        // Log::debug("user found: {$user->name}");
        User::where('name', $userName)
            ->firstOrFail()
            ->update([
                'public_key' => $publicKey,
            ])
            ;
        // broadcast a "user joins" event with this user name and public key
        UserJoined::dispatch($userName, $publicKey);
        // return the other user's info
        $otherUserName = $userName === 'Alice' ? 'Bob' : 'Alice';
        $otherUser = User::where('name', $otherUserName)->firstOrFail();
        $jsonResponseData = [
            'otherUser' => [
                'name' => $otherUser->name,
                'publicKey' => $otherUser->public_key,
            ],
        ];
        return $jsonResponseData;
    }

    public static function handleSendMessage(string $from, string $to, string $message): array {
        Log::debug('ChatroomService::handleSendMessage called');
        Log::debug("from: {$from}");
        Log::debug("to: {$to}");
        Log::debug("message: {$message}");
        RelayMessage::dispatch($from, $to, $message);
        return [
            'success' => true,
            'message' => 'message sent',
        ];
    }
}
