<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Customer\AccountCreated' => [
            'App\Listeners\Customer\AccountCreatedListener',
        ],
        'App\Events\Customer\AccountActivated' => [
            'App\Listeners\Customer\AccountActivatedListener',
        ],
        'App\Events\Customer\MomentCreated' => [
            'App\Listeners\Customer\MomentCreatedListener',
        ],
        'App\Events\Customer\MomentGuestsInvited' => [
            'App\Listeners\Customer\SendGuestInviteNotification',
        ],
        'App\Events\Customer\MomentOrganisersAdded' => [
            'App\Listeners\Customer\MomentOrganisersAddedListener',
        ],
        // 'App\Events\Customer\EmailUpdated' => [
        //     'App\Listeners\Customer\SendEmailUpdatedNotification'
        // ],
        // 'App\Events\Customer\PhoneUpdated' => [
        //     'App\Listeners\Customer\SendPhoneUpdatedNotification'
        // ],
        // 'App\Events\Customer\PasswordChanged' => [
        //     'App\Listeners\Customer\SendPasswordChangedNotification'
        // ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
