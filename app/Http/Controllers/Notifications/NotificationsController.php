<?php

namespace App\Http\Controllers\Notifications;
use Illuminate\Contracts\Support\Responsable;
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

    public function notificationsCount(Request $request){
        $user = request()->user();
        $notificationCountResponse = DB::table('notifications')->select('*')->where('user_id', '=', $user->id)->where('is_Read', '=', '0')->count();

        return response()->json(["notificationsCount" => $notificationCountResponse]);
    }

    public function markAsRead(Request $request){
        $user = request()->user();

        $request->validate([
            'notificationId' => 'required|integer',
        ]);

        DB::table('notifications')->where('user_id', '=', $user->id)->where('id', '=', $request->notificationId)->update([
            'is_read'=> true,
        ]);

        return response()->json(["status" => true, "message"=> 'Notification has been read']);


    }

}
