<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerAvatarController extends Controller
{
    /**
     * Update the avatar for the user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
    	$avatar = $request->file('avatar');
        $path = $avatar->store('avatars');

        return $path;
    }
}
