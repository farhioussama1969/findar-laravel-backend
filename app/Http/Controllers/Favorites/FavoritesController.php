<?php

namespace App\Http\Controllers\Favorites;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoritesController extends Controller
{
    public function addToFavorites(Request $request){
        $request->validate([
            'advertisementId' => 'required|integer',
        ]);

        $user = request()->user();
        $advertisementId = $request->advertisementId;

        $checkIfFavoriteExist = DB::table('favorites')->select('*')->where('user_id','=' ,"{$user->id}")->where('advertisement_id','=' ,"{$advertisementId}")->first();

        if(is_null($checkIfFavoriteExist)){
            DB::table('favorites')->insert([
                'user_id' => $user->id,
                'advertisement_id' => $advertisementId,
                'created_at' => now()
            ]);
            return response()->json(["success" => true, "message" => "Successfully added to favorite"]);
        }
        else{
            return response()->json(["success" => false, "message" => "Already added to favorite"]);
        }
    }

    public function deleteFromFavorites(Request $request){
        $request->validate([
            'advertisementId' => 'required|integer',
        ]);

        $user = request()->user();
        $advertisementId = $request->advertisementId;

        $checkIfFavoriteExist = DB::table('favorites')->select('*')->where('user_id','=' ,"{$user->id}")->where('advertisement_id','=' ,"{$advertisementId}")->first();

        if(!is_null($checkIfFavoriteExist)){
            DB::table('favorites')->where('user_id','=' ,"{$user->id}")->where('advertisement_id','=' ,"{$advertisementId}")->delete();
            return response()->json(["success" => true, "message" => "Successfully deleted from favorite"]);
        }
        else{
            return response()->json(["success" => false, "message" => "Item not found in favorite"]);
        }
    }

    public function favoritesList(Request  $request){
        $lang = $request->header('lang');
        $user = request()->user();

        $advertisementsResponse = DB::table('advertisements')->select(
            'id',
            'type',
            'created_at',
            DB::raw('(SELECT link FROM advertisement_images WHERE advertisement_images.advertisement_id = advertisements.id LIMIT 1) AS image_link'),
            DB::raw("(SELECT name_{$lang} FROM categories WHERE id = advertisements.category_id) AS category"),
            DB::raw("(SELECT COUNT(*) FROM views WHERE advertisement_id = advertisements.id) AS views"),
            DB::raw("(SELECT ROUND(SUM(value)/COUNT(*), 1) FROM reviews WHERE advertisement_id = advertisements.id) AS reviews"),
            DB::raw("(SELECT COUNT(*) FROM reviews WHERE advertisement_id = advertisements.id) AS totalReviews"),
            DB::raw("(SELECT price FROM prices WHERE advertisement_id = advertisements.id) AS price"),
            DB::raw("(SELECT according FROM prices WHERE advertisement_id = advertisements.id) AS according"),
            DB::raw("(SELECT name_{$lang} FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id)) AS province"),
            DB::raw("(SELECT name_{$lang} FROM states WHERE id = (SELECT state_id FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id))) AS state"),
        )->where('user_id' , '=', "{$user->id}")->orderByDesc('created_at')->where('id', '=', DB::raw("(SELECT advertisement_id FROM favorites WHERE user_id = {$user->id} AND advertisement_id=advertisements.id)"),)->paginate();

        return $advertisementsResponse;
    }
}
