<?php

namespace App\Http\Controllers\Notifications;
use Kreait\Firebase\Factory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationsController extends Controller
{

    static public function sendNotification($fcmToken , $body){

        $factory = (new Factory)->withServiceAccount(__DIR__.'/firebase-config.json');
        $messaging = $factory->createMessaging();


        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withData($body);

        $messaging->send($message);
    }

//    public function testingNotification(Request $request){
//        $user = $request->user();
//        NotificationsController::sendNotification($user->fcm_token, 'hi', 'this is a testing notification');
//        return $user->fcm_token;
//    }



}
