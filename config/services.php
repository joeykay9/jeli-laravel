<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN', 'sandbox7b947b140b9d49e08265870edbc65770.mailgun.org'),
        'secret' => env('MAILGUN_SECRET', '2ebc9616534816329d63f31f225b7071-b892f62e-141e8035'),
    ],

    'ses' => [
        'key' => env('SES_KEY', 'AKIAJQ6VJTITCDXGFKNQ'),
        'secret' => env('SES_SECRET', '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'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

];
