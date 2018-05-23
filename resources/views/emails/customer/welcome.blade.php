@component('mail::message')
# Welcome to Jeli

Verify your account with the digits below and begin to make moments possible.

@component('mail::panel', ['url' => ''])
{{ $otp->otp }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent