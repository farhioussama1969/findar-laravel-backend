<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function  index(Request $request){
        $lang = $request->header('lang');
        $user = request()->user();


        $topSellAdvertisementsResponse = DB::table('advertisements')->select(
            'id',
            'type',
            'category_id',
            DB::raw('(SELECT link FROM advertisement_images WHERE advertisement_images.advertisement_id = advertisements.id LIMIT 1) AS image_link'),
            DB::raw("(SELECT name_{$lang} FROM categories WHERE id = advertisements.category_id) AS category"),
            DB::raw("(SELECT COUNT(*) FROM favorites WHERE user_id = {$user->id} AND advertisement_id = advertisements.id) AS favorite"),
            DB::raw("(SELECT COUNT(*) FROM views WHERE advertisement_id = advertisements.id) AS views"),
            DB::raw("(SELECT ROUND(SUM(value)/COUNT(*), 1) FROM reviews WHERE advertisement_id = advertisements.id) AS reviews"),
            DB::raw("(SELECT price FROM prices WHERE advertisement_id = advertisements.id) AS price"),
            DB::raw("(SELECT according FROM prices WHERE advertisement_id = advertisements.id) AS according"),
            DB::raw("(SELECT latitude FROM advertisement_location WHERE advertisement_id = advertisements.id) AS latitude"),
            DB::raw("(SELECT longitude FROM advertisement_location WHERE advertisement_id = advertisements.id) AS longitude"),
            DB::raw("(SELECT name_{$lang} FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id)) AS province"),
            DB::raw("(SELECT name_{$lang} FROM states WHERE id = (SELECT state_id FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id))) AS state"),
        )->where('type' ,'=', 'sell')->orderByDesc('created_at')->limit(6)->get();

        $topRentAdvertisementsResponse = DB::table('advertisements')->select(
            'id',
            'type',
            'category_id',
            DB::raw('(SELECT link FROM advertisement_images WHERE advertisement_images.advertisement_id = advertisements.id LIMIT 1) AS image_link'),
            DB::raw("(SELECT name_{$lang} FROM categories WHERE id = advertisements.category_id) AS category"),
            DB::raw("(SELECT COUNT(*) FROM favorites WHERE user_id = {$user->id} AND advertisement_id = advertisements.id) AS favorite"),
            DB::raw("(SELECT COUNT(*) FROM views WHERE advertisement_id = advertisements.id) AS views"),
            DB::raw("(SELECT ROUND(SUM(value)/COUNT(*), 1) FROM reviews WHERE advertisement_id = advertisements.id) AS reviews"),
            DB::raw("(SELECT price FROM prices WHERE advertisement_id = advertisements.id) AS price"),
            DB::raw("(SELECT according FROM prices WHERE advertisement_id = advertisements.id) AS according"),
            DB::raw("(SELECT latitude FROM advertisement_location WHERE advertisement_id = advertisements.id) AS latitude"),
            DB::raw("(SELECT longitude FROM advertisement_location WHERE advertisement_id = advertisements.id) AS longitude"),
            DB::raw("(SELECT name_{$lang} FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id)) AS province"),
            DB::raw("(SELECT name_{$lang} FROM states WHERE id = (SELECT state_id FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id))) AS state"),
        )->where('type' ,'=', 'rent')->orderByDesc('created_at')->limit(6)->get();

        $topStates = DB::table('states')->select(
            'id',
            "name_{$lang} AS name",
            'image_link',
            DB::raw("(SELECT COUNT(*) FROM advertisements WHERE id IN (SELECT advertisement_id FROM advertisement_location WHERE province_id IN (SELECT id FROM provinces WHERE state_id = states.id))) AS totalAdvertisements"),
        )->orderByDesc(DB::raw("(SELECT COUNT(*) FROM advertisements WHERE id IN (SELECT advertisement_id FROM advertisement_location WHERE province_id IN (SELECT id FROM provinces WHERE state_id = states.id)))"))->limit(6)->get();



        return response()->json([
            "topSellAdvertisement" => $topSellAdvertisementsResponse,
            "topRentAdvertisement" => $topRentAdvertisementsResponse,
            "topStates" => $topStates,
            ]);
    }
}
