<?php

namespace App\Listeners\Customer;

use App\Notifications\Customer\WelcomeMessage;
use App\Events\Customer\AccountActivated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class AccountActivatedListener implements ShouldQueue
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
        if(App::environment('production')) {
            $event->customer->notify(new WelcomeMessage($event->customer));

            Log::channel('slack')->info('New Jeli Customer', [
                'Name' => $event->customer->first_name ? $event->customer->first_name . ' ' . $event->customer->last_name : $event->customer->jelion,
                'Phone' => $event->customer->phone,
                'Email' => $event->customer->email,
            ]);
        }
    }
}
