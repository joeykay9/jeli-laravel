<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Moment;
use Illuminate\Support\Facades\Storage;

class MomentImageController extends Controller
{
    /**
     * Update the image for the moment.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, Moment $moment)
    {
        if($request->hasFile('image')){
            $image = $request->file('image');
            $path = Storage::putFile(
                        'moments', $image
                    ); //stores file in 'avatars' directory and returns the path

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
        Storage::delete($filename);

        return response()->json([
            'success' => true,
            'message' => 'Icon has been successfully removed'
        ], 200);
    }
}
