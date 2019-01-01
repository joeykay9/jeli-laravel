<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\Business;
use Illuminate\Support\Facades\Storage;

class BusinessAvatarController extends ApiController
{
    /**
     * Update the avatar for the user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, Business $business)
    {
        if($request->hasFile('avatar')){
            $filename = $business->uuid . '.jpg';
            $avatar = $request->file('avatar');
            $path = Storage::putFileAs(
                        'avatars', $avatar, $filename
                    );

            $url = Storage::url($path);

            $business->avatar = $url;
            $business->save();
        }

        return $business;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(business $business)
    {
        //Delete business avatar
        $filename = 'avatars/' . $business->uuid . '.jpg';
        Storage::delete($filename);

        return response()->json([
            'success' => true,
            'message' => 'Avatar has been successfully removed'
        ], 200);
    }
}
