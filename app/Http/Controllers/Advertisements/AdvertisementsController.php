<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Image;


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
            'location' => 'required_with:locationSearchRange|array|min:2|max:2',
            'location.*' => 'numeric',
            'locationSearchRange' =>  'required_with:location|integer|between:100,2000',
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

        if($request->filled('location')) {
            $location = $requestInputs['location'];
            $locationSearchRange = $requestInputs['locationSearchRange'];
        }

        $advertisementsResponse = DB::table('advertisements')->select(
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
            $request->filled('location')? DB::raw(" (SELECT ROUND(ST_Distance_Sphere(point({$location[1]},{$location[0]}), point(7.9384,36.2801)))) AS distance"): DB::raw("(SELECT null) AS distance"),
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


        if($state != 0 && !$request->filled('location')){
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

        if($request->filled('location')){
            $advertisementsResponse->where(DB::raw("(SELECT ST_Distance_Sphere(point({$location[1]},{$location[0]}), point((SELECT longitude FROM advertisement_location WHERE advertisement_id = advertisements.id),(SELECT latitude FROM advertisement_location WHERE advertisement_id = advertisements.id))))"), '<=' , $locationSearchRange);
        }


        return $advertisementsResponse->paginate(10);

    }

    public function pricesRange(){
        $minRentPrice = DB::table('prices')->select(
            DB::raw('(SELECT MAX(price) FROM prices WHERE (SELECT type FROM advertisements WHERE id = prices.advertisement_id) = "sell") AS maxSellPrice'),
            DB::raw('(SELECT MAX(price) FROM prices WHERE (SELECT type FROM advertisements WHERE id = prices.advertisement_id) = "rent") AS maxRentPrice'),
            DB::raw('(SELECT MAX(total_area) FROM properties) AS maxArea'),
        )->limit(1)->first();
        return $minRentPrice;
    }

    public function advertisementDetails(Request $request, $id){

        $request->merge(['advertisementId' => $id]);

        $request->validate([
            'advertisementId' => 'required|integer',
        ]);

        $user = request()->user();
        $lang = $request->header('lang');

        DB::table('views')->insert([
            'user_id' => $user->id,
            'advertisement_id' => $id,
            'created_at' => now()
        ]);

        $advertisementDetails = DB::table('advertisements')->select(
            'id',
            'description',
            'type',
            'created_at',
            'category_id',
            DB::raw("(SELECT name_{$lang} FROM categories WHERE id = advertisements.category_id) AS category"),
            DB::raw("(SELECT price FROM prices WHERE advertisement_id = advertisements.id) AS price"),
            DB::raw("(SELECT negotiable FROM prices WHERE advertisement_id = advertisements.id) AS negotiable"),
            DB::raw("(SELECT according FROM prices WHERE advertisement_id = advertisements.id) AS according"),
            DB::raw("(SELECT name_{$lang} FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id)) AS province"),
            DB::raw("(SELECT name_{$lang} FROM states WHERE id = (SELECT state_id FROM provinces WHERE id = (SELECT province_id FROM advertisement_location WHERE advertisement_id = advertisements.id))) AS state"),
            DB::raw("(SELECT COUNT(*) FROM views WHERE advertisement_id = advertisements.id) AS views"),
            DB::raw("(SELECT ROUND(SUM(value)/COUNT(*), 1) FROM reviews WHERE advertisement_id = advertisements.id) AS reviews"),
            DB::raw("(SELECT COUNT(*) FROM reviews WHERE advertisement_id = advertisements.id) AS totalReviews"),
            DB::raw("(SELECT COUNT(*) FROM favorites WHERE user_id = {$user->id} AND advertisement_id = advertisements.id) AS favorite"),
            DB::raw("(SELECT latitude FROM advertisement_location WHERE advertisement_id = advertisements.id) AS latitude"),
            DB::raw("(SELECT longitude FROM advertisement_location WHERE advertisement_id = advertisements.id) AS longitude"),
        )->find($id);

        $advertisementOwner = DB::table('advertisements')->select(
            DB::raw("(SELECT id FROM users WHERE id = advertisements.user_id) AS id"),
            DB::raw("(SELECT name FROM users WHERE id = advertisements.user_id) AS name"),
            DB::raw("(SELECT phone FROM users WHERE id = advertisements.user_id) AS phone"),
            DB::raw("(SELECT COUNT(*) FROM advertisements WHERE user_id = (SELECT id FROM users WHERE id = advertisements.user_id)) AS totalAdvertisements"),
        )->find($id);

        $advertisementImages = DB::table('advertisement_images')->select('link')->where('advertisement_id', '=', "{$id}")->get();
        $advertisementProperties = DB::table('properties')->select('*')->where('advertisement_id', '=', "{$id}")->get();
        $advertisementFeatures = DB::table('features')->select('*')->where('advertisement_id', '=', "{$id}")->get();
        $advertisementTopReviews = DB::table('reviews')->select('value',
            'comment',
            'created_at',
            DB::raw("(SELECT name FROM users WHERE id = reviews.user_id) AS name"),
        )->where('advertisement_id', '=', "{$id}")->limit(3)->get();


        return response()->json(["success" => true,
            "advertisementDetails" => $advertisementDetails,
            "advertisementOwner"=> $advertisementOwner,
            "advertisementImages"=> $advertisementImages,
            "advertisementProperties"=> $advertisementProperties,
            "advertisementFeatures"=> $advertisementFeatures,
            "advertisementTopReviews"=> $advertisementTopReviews,
            ]);
    }

    public function myAdvertisementsList(Request $request){
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
        )->where('user_id' , '=', "{$user->id}")->orderByDesc('created_at')->paginate();

        return $advertisementsResponse;
    }

    public function deleteAdvertisement(Request $request){
        $user = request()->user();

        $request->validate([
            'advertisementId' => 'required|integer',
        ]);

        $advertisementId = $request->advertisementId;
        $checkIfAdvertisementExist = DB::table('advertisements')->select('*')->where('user_id','=' ,"{$user->id}")->where('id','=' ,"{$advertisementId}")->first();

        if(!is_null($checkIfAdvertisementExist)){
            DB::table('advertisements')->where('user_id','=' ,"{$user->id}")->where('id','=' ,"{$advertisementId}")->delete();
            return response()->json(["success" => true, "message" => "Advertisements deleted successfully"]);
        }
        else{
            return response()->json(["success" => false, "message" => "advertisements not found"]);
        }
    }

    public function addAdvertisement(Request $request){
        $user = request()->user();

        $request->validate([
            'description' => 'required',
            'type' => 'required|in:'.implode(",", ["rent", "sell"]),
            'categoryId' => 'required|in:'.implode(",", [1, 2, 3, 4, 5, 6]),
            'location' => 'required|array|min:2|max:2',
            'location.*' => 'numeric',
            'price' => 'required|numeric',
            'according' => 'required_if:type,==,rent|in:'.implode(",", ["month" , "year", "day"]),
            'negotiable' => 'required|in:'.implode(",", [0, 1]),
            'properties' => 'required|array|min:1',
            'properties.*.totalArea' => 'required|numeric',
            'properties.*.builtArea' => 'required_if:categoryId,==,1,3|numeric',
            'properties.*.numberOfRooms' => 'required_if:categoryId,==,1,2,3,5|numeric',
            'properties.*.floorNumber' => 'required_if:categoryId,==,2,4,5|numeric',
            'properties.*.numberOfFloor' => 'required_if:categoryId,==,1,3|numeric',
            'properties.*.numberOfBathrooms' => 'required_if:categoryId,==,1,3,5|numeric',
            'properties.*.numberOfKitchen' => 'required_if:categoryId,==,1,3,5|numeric',
            'properties.*.numberOfGarages' => 'required_if:categoryId,==,1,3|numeric',
            'properties.*.numberOfBalcony' => 'required_if:categoryId,==,1,2,3,4,5|numeric',
            'properties.*.isFurnished' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features' => 'array',
            'features.*.conditioner' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.heating' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.electricity' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.gas' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.water' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.tvCable' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.fixedTelephoneCable' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.fiberInternetCable' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.refrigerator' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.washer' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.waterTank' => 'required_if:categoryId,==,1,2,3,4,5|in:'.implode(",", [0, 1]),
            'features.*.pool' => 'required_if:categoryId,==,3|in:'.implode(",", [0, 1]),
            'features.*.garden' => 'required_if:categoryId,==,1,3|in:'.implode(",", [0, 1]),
            'features.*.elevator' => 'required_if:categoryId,==,2,4|in:'.implode(",", [0, 1]),
            'images' => 'required|array|min:1',
            'images.*' => 'image',
        ]);

        $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$request->location[1]},{$request->location[0]}.json?types=country%2Cregion%2Cplace%2Cpostcode&language=en,ar,fr&access_token=pk.eyJ1IjoiZmFyaGlvdXNzYW1hMTk2OSIsImEiOiJjbDIwaTBrNjUwMmJjM2NtcXN2MXpoN2NrIn0.JYwciK8JtIqu1GZW1D73Dg");

        $body = json_decode($response->body(), true);

        //country
        $conutry = $body['features'][0]['context'][2]['text_en'];
        //state
        $state_en = $body['features'][0]['context'][1]['text_en'];
        $state_ar = $body['features'][0]['context'][1]['text_ar'];
        $state_fr = $body['features'][0]['context'][1]['text_fr'];
        //province
        $province_en = $body['features'][0]['context'][0]['text_en'];
        $province_ar = $body['features'][0]['context'][0]['text_ar'];
        $province_fr = $body['features'][0]['context'][0]['text_fr'];

        if($conutry != 'Algeria'){
            return response()->json(["success" => false, "message" => "The service is not available in this country"]);
        }else{

            $advertisementId = DB::table('advertisements')->insertGetId([
                'description' => $request->description,
                'type' => $request->type,
                'category_id' => $request->categoryId,
                'user_id' => $user->id,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);


            //location start

            $checkIfProvinceExist = DB::table('provinces')->select('id')->where('name_en','=' ,"{$province_en}")->first();

            if(is_null($checkIfProvinceExist)){

               $stateId = DB::table('states')->select('id')->where('name_en','=' ,"{$state_en}")->first();

               $provinceId = DB::table('provinces')->insertGetId([
                    'name_ar' => $province_ar,
                    'name_en' => $province_en,
                    'name_fr' => $province_fr,
                    'state_id' => $stateId->id,
                ]);

               if(is_null($request->address)){
                   $address = null;
               }else{
                   $address = $request->address;
               }

                DB::table('advertisement_location')->insert([
                    'advertisement_id' => $advertisementId,
                    'province_id' => $provinceId,
                    'latitude' => $request->location[0],
                    'longitude' => $request->location[1],
                    'address' => $address,
                ]);

            }
            else{

                DB::table('advertisement_location')->insert([
                    'advertisement_id' => $advertisementId,
                    'province_id' => $checkIfProvinceExist->id,
                    'latitude' => $request->location[0],
                    'longitude' => $request->location[1],
                    'address' => $request->address ?? null,
                ]);

            }

            //location end

            //price start

            DB::table('prices')->insert([
                'advertisement_id' => $advertisementId,
                'price' => $request->price,
                'negotiable' => $request->negotiable,
                'according' => $according = $request->according ?? null,
            ]);

            //price end


            //properties start
            DB::table('properties')->insert([
                'advertisement_id' => $advertisementId,
                'floor_number' => $request->properties[0]['floorNumber'] ?? null,
                'number_of_rooms' => $request->properties[0]['numberOfRooms'] ?? null,
                'number_of_floor' => $request->properties[0]['numberOfFloor'] ?? null,
                'number_of_bathrooms' => $request->properties[0]['numberOfBathrooms'] ?? null,
                'total_area' => $request->properties[0]['totalArea'],
                'built_area' => $request->properties[0]['builtArea'] ?? null,
                'number_of_kitchen' => $request->properties[0]['numberOfKitchen'] ?? null,
                'number_of_garages' => $request->properties[0]['numberOfGarages'] ?? null,
                'number_of_balcony' => $request->properties[0]['numberOfBalcony'] ?? null,
                'is_furnished' => $request->properties[0]['isFurnished'] ?? null,
            ]);
            //properties end

            //features start
            DB::table('features')->insert([
                'advertisement_id' => $advertisementId,
                'conditioner' => $request->features[0]['conditioner'] ?? null,
                'heating' => $request->features[0]['heating'] ?? null,
                'electricity' => $request->features[0]['electricity'] ?? null,
                'gas' => $request->features[0]['gas'] ?? null,
                'water' => $request->features[0]['water'] ?? null,
                'tv_cable' => $request->features[0]['tvCable'] ?? null,
                'fixed_telephone_cable' => $request->features[0]['fixedTelephoneCable'] ?? null,
                'fiber_internet_cable' => $request->features[0]['fiberInternetCable'] ?? null,
                'refrigerator' => $request->features[0]['refrigerator'] ?? null,
                'washer' => $request->features[0]['washer'] ?? null,
                'water_tank' => $request->features[0]['waterTank'] ?? null,
                'pool' => $request->features[0]['pool'] ?? null,
                'garden' => $request->features[0]['garden'] ?? null,
                'elevator' => $request->features[0]['elevator'] ?? null,
            ]);
            //features end

            //images start
                $images = $request->file('images');
                foreach ($images as $image){
                    $imageName = $advertisementId . '-' . rand() . '.'.$image->getClientOriginalExtension();

                    //thumbnail image
                    $thumbnail = Image::make($image->getRealPath());
                    $thumbnail->resize(100, 100, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save(public_path('/uploads/advertisements-images/thumbnail').'/'.$imageName);
                    $thumbnailLink = 'https://findar-api.duo-mart.com/public/uploads/advertisements-images/thumbnail/' . $imageName;

                    //original image
                    $image->move(public_path('/uploads/advertisements-images/'), $imageName);
                    $imageLink = 'https://findar-api.duo-mart.com/public/uploads/advertisements-images/' . $imageName;


                    DB::table('advertisement_images')->insert([
                        'advertisement_id' => $advertisementId,
                        'link' => $imageLink,
                        'thumbnail' => $thumbnailLink,
                    ]);
                }
            //images end

        }







        return response()->json(["success" => true, "message" => "Advertisement added successfully"]);

    }
}
