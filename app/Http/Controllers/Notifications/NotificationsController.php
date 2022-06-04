<?php

namespace App\Http\Controllers\Notifications;
use Kreait\Firebase\Factory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationsController extends Controller
{

    static public function sendNotification($fcmToken , $body){

        $factory = (new Factory)->withServiceAccount(__DIR__.'/firebase-config.json');
        $messaging = $factory->createMessaging();

        $config = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'normal',
            'notification' => [
                'title' => '$GOOG up 1.43% on the day',
                'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                'icon' => 'stock_ticker_update',
                'color' => '#f45342',
                'sound' => 'default',
            ],
        ]);


        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withData($body)->withAndroidConfig($config);

        $messaging->send($message);
    }

//    public function testingNotification(Request $request){
//        $user = $request->user();
//        NotificationsController::sendNotification($user->fcm_token, 'hi', 'this is a testing notification');
//        return $user->fcm_token;
//    }



}
