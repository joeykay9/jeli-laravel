<?php

namespace App\Listeners\Customer;

use App\Events\Customer\AccountActivated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountActivatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AccountActivated  $event
     * @return void
     */
    public function handle(AccountActivated $event)
    {
        $event->customer->notify(new WelcomeMessage($event->customer));
    }
}
