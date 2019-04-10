<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Customer;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\API\ApiController;

class CustomerAvatarController extends ApiController
{
    public function __construct(){
        $this->middleware('auth:api');
    }

    /**
     * Update the avatar for the user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, Customer $customer)
    {
        dd($request->all('avatar'));
        if($request->hasFile('avatar')){
            $avatar = $request->file('avatar');
            $path = Storage::putFile(
                        'public/avatars', $avatar
                    ); //stores file in 'avatars' directory and returns the path

            dd($path);

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

        $customer->avatar = null;
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar has been successfully removed'
        ], 200);
    }
}
