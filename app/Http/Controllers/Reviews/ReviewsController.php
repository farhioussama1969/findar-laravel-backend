<?php

namespace App\Http\Controllers\Reviews;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Notifications\NotificationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewsController extends Controller
{
    public function reviewsList(Request $request, $id){
        $request->merge(['advertisementId' => $id]);

        $request->validate([
            'advertisementId' => 'required|integer',
        ]);

        $reviews = DB::table('reviews')->select(
            'id', 'value', 'created_at', 'comment',
            DB::raw("(SELECT name FROM users WHERE id = reviews.user_id) AS userName"),
        )->where('advertisement_id', '=', "{$id}")->paginate();

        return $reviews;

    }


    public function addReview(Request $request){
        $request->validate([
            'advertisementId' => 'required|integer',
            'value'=> 'required|in:'.implode(",", [1, 2, 3, 4, 5]),
            'comment'=> 'required',
        ]);

        $user = request()->user();

        DB::table('reviews')->insert([
            'user_id' => $user->id,
            'advertisement_id' => $request->advertisementId,
            'comment'=> $request->comment,
            'value'=> $request->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $targetUser = DB::table('users')->select('*')->where('id', '=', DB::raw("(SELECT user_id FROM advertisements WHERE advertisements.id = {$request->advertisementId})"))->first();
        //You have been rated and commented on your advertisement
        DB::table('notifications')->insert([
            'type' => 'review',
            'title' => 'New review',
            'body' => "{
                        'en':'You have been rated and commented on your advertisement',
                        'ar':'لقد تم تصنيفك والتعليق على إعلانك',
                        }",
            'is_read' => 0,
            'user_id' => $targetUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificationsController::sendNotification($targetUser->fcm_token, 'review',
            response()->json(["success" => true, "message" => "Successfully added to reviews"]));

        return response()->json(["success" => true, "message" => "Successfully added to reviews"]);
    }
}
