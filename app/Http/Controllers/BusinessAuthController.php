<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class BusinessAuthController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/faqs';

    public function __construct()
    {
    	$this->middleware('guest');
    }
}
