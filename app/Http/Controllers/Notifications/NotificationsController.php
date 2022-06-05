<?php

namespace App\Http\Controllers\Notifications;
use Illuminate\Support\Facades\DB;
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
            'priority' => 'high',
        ]);


        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withData($body)->withAndroidConfig($config);

        $messaging->send($message);
    }


    public function notificationsList(Request $request){
        $user = request()->user();

        $notificationsResponse = DB::table('notifications')->select('*')->where('user_id', '=', $user->id)->orderByDesc('created_at')->paginate(10);

        return $notificationsResponse;

    }

}
