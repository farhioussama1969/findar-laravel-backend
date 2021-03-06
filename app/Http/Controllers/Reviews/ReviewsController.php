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
        )->where('advertisement_id', '=', "{$id}")->orderByDesc('created_at')->paginate();

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
        $advertisementImage= DB::table('advertisement_images')->select('link')->where('advertisement_id', '=', $request->advertisementId)->limit(1)->first();
        $advertisementThumbnail= DB::table('advertisement_images')->select('thumbnail')->where('advertisement_id', '=', $request->advertisementId)->limit(1)->first();
        //You have been rated and commented on your advertisement
        DB::table('notifications')->insert([
            'type' => 'review',
            'data' => json_encode(
                [
                    'type'=> 'review',
                    'title_ar'=> '?????????? ????????',
                    'title_en'=> 'New comment',
                    'title_fr'=> 'nouveau commentaire',
                    'body_ar' => "?????? ?????????? ?????????? ???????? ?????? ?????????????? ??????: {$request->advertisementId}#",
                    'body_en' => "You have received a new comment on your advertisement Num:# {$request->advertisementId}",
                    'body_fr' => "Vous avez re??u un nouveau commentaire sur votre annonce Num:# {$request->advertisementId}",
                    'user' => $user->name,
                    'comment' => $request->comment,
                    'value' => $request->value,
                    'advertisementImage' => $advertisementImage->link,
                    'advertisementThumbnail' => $advertisementThumbnail->thumbnail,
                    'advertisementId' => $request->advertisementId,
                ]
            ),
            'is_read' => 0,
            'user_id' => $targetUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        NotificationsController::sendNotification($targetUser->fcm_token,
                [
                    'type'=> 'review',
                    'title_ar'=> '?????????? ????????',
                    'title_en'=> 'New comment',
                    'title_fr'=> 'nouveau commentaire',
                    'body_ar' => "?????? ?????????? ?????????? ???????? ?????? ?????????????? ??????: {$request->advertisementId}#",
                    'body_en' => "You have received a new comment on your advertisement Num:# {$request->advertisementId}",
                    'body_fr' => "Vous avez re??u un nouveau commentaire sur votre annonce Num:# {$request->advertisementId}",
                    'user' => $user->name,
                    'comment' => $request->comment,
                    'value' => $request->value,
                    'advertisementImage' => $advertisementImage->link,
                    'advertisementId' => $request->advertisementId,
                ]
            );

        return response()->json(["success" => true, "message" => "Successfully added to reviews"]);
    }
}
