<?php

namespace App\Http\Controllers\Reviews;

use App\Http\Controllers\Controller;
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
        return response()->json(["success" => true, "message" => "Successfully added to reviews"]);
    }
}
