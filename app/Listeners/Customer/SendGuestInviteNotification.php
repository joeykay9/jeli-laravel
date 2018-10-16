<?php

namespace App\Listeners\Customer;

use App\Events\Customer\MomentGuestsInvited;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendGuestInviteNotification implements ShouldQueue
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
     * @param  MomentGuestsInvited  $event
     * @return void
     */
    public function handle(MomentGuestsInvited $event)
    {
        Notification::send($event->jeliContacts, new GuestInvite($event->customer, $event->moment));
    }
}
