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
    public function index(Moment $moment)
    {
        $members = $moment->members()->get(); //will add take(10) later

        return $this->respondWithCollection($members, new MembersTransformer);
    }
}
