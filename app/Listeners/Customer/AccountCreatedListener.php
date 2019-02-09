<?php

namespace App\Listeners\Customer;

use App\Events\Customer\AccountCreated;
use App\Notifications\Customer\WelcomeMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AccountCreatedListener implements ShouldQueue
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
     * @param  AccountCreated  $event
     * @return void
     */
    public function handle(AccountCreated $event)
    {
        
    }
}
