<?php

namespace App\Firebase;

use Firebase\Auth\Token\Verifier;
use App\Customer;

class Guard
{
    protected $verifier;

    public function __construct(Verifier $verifier)
    {
        $this->verifier = $verifier;
    }

    public function user($request)
    {
        $token = $request->bearerToken();
        
        try {
            $token = $this->verifier->verifyIdToken($token);
            return new Customer($token->getClaims());
        }
        catch (\Exception $e) {
            return;
        }
    }
}