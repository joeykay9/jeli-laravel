<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Customer;
use Illuminate\Support\Facades\Storage;

class CustomerAvatarController extends Controller
{
    /**
     * Update the avatar for the user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, Customer $customer)
    {
        if($request->hasFile('avatar')){
            $avatar = $request->file('avatar');
            $path = Storage::putFile(
                        'avatars', $avatar
                    ); //stores file in 'avatars' directory and returns the path

            $url = Storage::url($path); //returns full url of location of file

            $customer->avatar = $url;
            $customer->save();
        }

        return $customer;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        //Delete customer avatar
        $url = $customer->avatar; //get the url of the avatar
        $path = substr(parse_url($url, PHP_URL_PATH), 1); //returns path of url with leading / taken off
        Storage::delete($path);

        return response()->json([
            'success' => true,
            'message' => 'Avatar has been successfully removed'
        ], 200);
    }
}
