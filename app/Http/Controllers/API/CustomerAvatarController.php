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
                    );

            $url = Storage::url($path);

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
        $filename = 'avatars/' . $customer->uuid . '.jpg';
        Storage::delete($filename);

        return response()->json([
            'success' => true,
            'message' => 'Avatar has been successfully removed'
        ], 200);
    }
}
