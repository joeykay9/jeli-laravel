<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Moment;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\API\ApiController;

class MomentImageController extends ApiController
{

    public function __construct(){
        $this->middleware('auth:api');
        $this->middleware('moment.creator')->only([
            'update', 'destroy'
        ]);
    }

    /**
     * Update the image for the moment.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, Moment $moment)
    {
        if($request->hasFile('icon')){
            $image = $request->file('icon');
            $path = Storage::putFile(
                        'moments', $image
                    ); //stores file in 'moments' directory and returns the path

            $url = Storage::url($path); //returns full url of location of file

            $moment->icon = $url;
            $moment->save();
        }

        return $moment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Moment $moment)
    {
        //Delete moment icon
        $url = $moment->icon; //get the url of the moment
        $path = substr(parse_url($url, PHP_URL_PATH), 1); //returns path of url with leading / taken off
        // dd($path);
        Storage::delete($path);

        $moment->icon = null;
        $moment->save();

        return response()->json([
            'success' => true,
            'message' => 'Icon has been successfully removed'
        ], 200);
    }
}
