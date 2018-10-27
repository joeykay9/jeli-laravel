<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\Moment;
use App\Customer;
use App\Transformers\MembersTransformer;

class MomentMemberController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Moment $moment)
    {
        $members = $moment->members()->take(10)->get();

        return $this->respondWithCollection($members, new MembersTransformer);
    }
}
