<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Customer;

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
            $filename = $customer->uuid . '.jpg';
            $avatar = $request->file('avatar');
            $path = $avatar->storeAs('avatars', $filename);

            $directory = config('app.url') . '/storage/app/avatars/';
            $customer->avatar = $directory . $filename;
            $customer->save();
        }

        return $customer;
    }
}
