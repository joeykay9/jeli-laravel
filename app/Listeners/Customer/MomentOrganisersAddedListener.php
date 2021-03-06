<?php

namespace App\Listeners\Customer;

use App\Events\Customer\MomentOrganisersAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Customer\OrganiserAddedNotification;
use Illuminate\Support\Facades\Notification;

class MomentOrganisersAddedListener
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
     * @param  MomentOrganisersAdded  $event
     * @return void
     */
    public function handle(MomentOrganisersAdded $event)
    {
        //Might use a Job to do this or not
        Notification::send($event->jeliOrganisers, new OrganiserAddedNotification($event->customer, $event->moment));
    }
}
