<?php

namespace App\Http\Controllers\States;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Image;

class StatesController extends Controller
{
    public function statesList(Request $request){
        $lang = $request->header('lang');
        $statesResponse = DB::table('states')->select('id', "name_{$lang} AS name", 'latitude', 'longitude')->orderBy('id')->paginate(48);
        return $statesResponse;
    }

    public function addStates(Request $request){
        $request->validate([
            'stateId' => 'required|integer',
            'location' => 'required|array|min:2|max:2',
            'location.*' => 'numeric',
            'image' => 'required|image',
        ]);

        $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$request->location[1]},{$request->location[0]}.json?types=country%2Cregion%2Cplace%2Cpostcode&language=en,ar,fr&access_token=pk.eyJ1IjoiZmFyaGlvdXNzYW1hMTk2OSIsImEiOiJjbDIwaTBrNjUwMmJjM2NtcXN2MXpoN2NrIn0.JYwciK8JtIqu1GZW1D73Dg");
        $body = json_decode($response->body(), true);

        $conutry = $body['features'][0]['context'][2]['text_en'];
        $state_en = $body['features'][0]['context'][1]['text_en'];
        $state_ar = $body['features'][0]['context'][1]['text_ar'];
        $state_fr = $body['features'][0]['context'][1]['text_fr'];

        if($conutry != 'Algeria'){
            return response()->json(["success" => false, "message" => "The service is not available in this country"]);
        }else{

            $image = $request->file('image');
            $imageName = $request->stateId . '-' . rand() . '.'.$image->getClientOriginalExtension();

            //thumbnail image
            $thumbnail = Image::make($image->getRealPath());
            $thumbnail->resize(100, 100, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path('/uploads/states-images/thumbnail').'/'.$imageName);
            $thumbnailLink = 'https://findar-api.duo-mart.com/public/uploads/states-images/thumbnail/' . $imageName;

            //original image
            $image->move(public_path('/uploads/states-images/'), $imageName);
            $imageLink = 'https://findar-api.duo-mart.com/public/uploads/states-images/' . $imageName;

            DB::table('states')->insertOrIgnore(
                [
                    'id'=> $request->stateId,
                    'name_ar'=> $request->$state_ar,
                    'name_en'=> $request->$state_en,
                    'name_fr'=> $request->$state_fr,
                    'latitude'=> $request->location[0],
                    'longitude'=> $request->location[1],
                    'image_link'=> $imageLink,
                    'thumbnail_link'=> $thumbnailLink,
                ]
            );

            return response()->json(["success" => true, "message" => "State added successfully", "stateName"=>$state_ar]);
        }



    }
}
