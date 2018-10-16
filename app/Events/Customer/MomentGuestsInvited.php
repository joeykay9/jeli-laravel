<?php

namespace App\Events\Customer;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Moment;
use App\Customer;

class MomentGuestsInvited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $customer;
    public $moment;
    public $jeliContacts;
    public $nonJeliContacts;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, Moment $moment, $jeliContacts, $nonJeliContacts)
    {
        $this->customer = $customer;
        $this->moment = $moment;
        $this->jeliContacts = $jeliContacts;
        $this->nonJeliContacts = $nonJeliContacts;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
