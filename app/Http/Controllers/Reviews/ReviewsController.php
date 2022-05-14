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
        )->paginate();

        return $reviews;

    }
}
