<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\LiveDataUpdated;
use App\Events\UserNotification;

class BroadcastController extends Controller
{
    // Trigger a public live-data broadcast (for testing)
    public function sendLiveData(Request $request)
    {
        $payload = $request->input('data', [
            'time' => now()->toDateTimeString(),
            'payload' => $request->input('payload', null),
        ]);

        event(new LiveDataUpdated($payload));

        return response()->json(['ok' => true, 'data' => $payload]);
    }

    // Send a private notification to a user (will broadcast on private App.Models.User.{id})
    public function notifyUser(Request $request, $userId)
    {
        $message = $request->input('message', 'You have a new notification');
        event(new UserNotification($userId, $message));
        return response()->json(['ok' => true]);
    }
}
