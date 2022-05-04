<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvertisementsController extends Controller
{
    public function advertisementsList(Request $request){
        $lang = $request->header('lang');

        $request->validate([
            'type' => 'required|array|min:1|max:2',
            'type.*' => 'required|in:'.implode(",", ["rent", "sell"]),
            'state' => 'required|digits_between:0,2',
            'categories' => 'required|array|min:1|max:6',
            'categories.*' => 'required|in:'.implode(",", [1, 2, 3, 4, 5, 6]),
            'numberOfRooms' => 'required|array',
            'numberOfRooms.*' => 'required|in:'.implode(",", [1, 2, 3, 4, 5, 6 ,7 ,8]),
            'priceRange' => 'required|array|min:2|max:2',
            'priceRange.*' => 'required|integer',
            'areaRange' => 'required|array|min:2|max:2',
            'areaRange.*' => 'required|integer',
            'sortByNewest' => 'required|in:'.implode(",", [0 , 1]),
            'sortByPrice' => 'required|in:'.implode(",", [0 , 1 , 2]),
            'sortByArea' => 'required|in:'.implode(",", [0 , 1 , 2]),
        ]);


        $user = request()->user();

        $requestInputs = $request->all();
        //filter parameters
        $type = $requestInputs['type'];
        $state = $requestInputs['state'];
        $categories = $requestInputs['categories'];
        $numberOfRooms = $requestInputs['numberOfRooms'];
        $priceRange = $requestInputs['priceRange'];
        $areaRange = $requestInputs['areaRange'];

        //sort parameters
        $sortByNewest = $requestInputs['sortByNewest'];
        $sortByPrice = $requestInputs['sortByPrice'];
        $sortByArea = $requestInputs['sortByArea'];




        $advertisementsResponse = DB::table('advertisements')->select(
            'id',
            'type',
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
        )->whereIn('type' , $type)->whereIn('category_id', $categories);

        if(!in_array(8, $numberOfRooms)){
            $advertisementsResponse->where(function ($query) use ($numberOfRooms) {
                $query->whereIn(DB::raw("(SELECT number_of_rooms FROM properties WHERE advertisement_id = advertisements.id)") , $numberOfRooms)->orWhereNull(DB::raw("(SELECT number_of_rooms FROM properties WHERE advertisement_id = advertisements.id)"));
            });
        }
        else{
            $advertisementsResponse->where(function ($query) use ($numberOfRooms) {
                $query->where(DB::raw("(SELECT number_of_rooms FROM properties WHERE advertisement_id = advertisements.id)") , '>=', 8)->orWhereIn(DB::raw("(SELECT number_of_rooms FROM properties WHERE advertisement_id = advertisements.id)"), $numberOfRooms)->orWhereNull(DB::raw("(SELECT number_of_rooms FROM properties WHERE advertisement_id = advertisements.id)"));;
            });
        }

        $advertisementsResponse->whereBetween(DB::raw("(SELECT price FROM prices WHERE advertisement_id = advertisements.id)"),$priceRange);
        $advertisementsResponse->whereBetween(DB::raw("(SELECT total_area FROM properties WHERE advertisement_id = advertisements.id)"),$areaRange);


        if($state != 0){
            $advertisementsResponse->where(DB::raw("(SELECT id FROM states WHERE id = (SELECT state_id FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id)))"),'=', $state);
        }


        if($sortByNewest == 1){
            $advertisementsResponse->orderByDesc('created_at');
        }

        if($sortByPrice == 1){
            $advertisementsResponse->orderByDesc(DB::raw("(SELECT price FROM prices WHERE advertisement_id = advertisements.id)"));
        }
        else if($sortByPrice == 2){
            $advertisementsResponse->orderBy(DB::raw("(SELECT price FROM prices WHERE advertisement_id = advertisements.id)"));
        }

        if($sortByArea == 1){
            $advertisementsResponse->orderByDesc(DB::raw("(SELECT total_area FROM properties WHERE advertisement_id = advertisements.id)"));
        }else if($sortByArea == 2){
            $advertisementsResponse->orderBy(DB::raw("(SELECT total_area FROM properties WHERE advertisement_id = advertisements.id)"));

        }


        return $advertisementsResponse->paginate();

    }
}
