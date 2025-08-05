<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FcmService
{
    public function send(string $token, string $title, string $body): array
    {
        $response = Http::withToken(env('FCM_SERVER_KEY'))
            ->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ]);

        return $response->json();
    }
}
