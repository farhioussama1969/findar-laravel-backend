<?php

namespace App\Http\Controllers\Notifications;
use Kreait\Firebase\Factory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationsController extends Controller
{

    public function sendNotification($fcmToken , $title , $body){

        $factory = (new Factory)->withServiceAccount(__DIR__.'/firebase-config.json');
        $messaging = $factory->createMessaging();


        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withNotification(Notification::create($title, $body));

        $message = CloudMessage::fromArray([
            'token' => $fcmToken,
            'notification' => [/* Notification data as array */], // optional
            'data' => [/* data array */], // optional
        ]);

        $messaging->send($message);
    }

    public function testingNotification(Request $request){
        $user = $request->user();
        NotificationsController::sendNotification($user->fcm_token, 'hi', 'this is a testing notification');
    }



}
