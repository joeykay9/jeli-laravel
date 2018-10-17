<?php

namespace App\Listeners\Customer;

use App\Events\Customer\MomentGuestsInvited;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Customer\GuestInvite;
use App\Customer;

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
        $guestInviteNotification = new GuestInvite($event->customer, $event->moment);

        //Might use a Job to do this or not
        Notification::send($event->jeliContacts, $guestInviteNotification);

        //Will use a Job to implement this later
        foreach ($event->nonJeliContacts as $key => $value) { //Send Guest Invite to invited non Jeli Contacts
            $customer = new Customer;
            $customer->id = 999; //to prevent SQL exception for database notification
            $customer->phone = $value;
            $customer->notify($guestInviteNotification);
        }
    }
}
