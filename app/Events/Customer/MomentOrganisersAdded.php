<?php

namespace App\Events\Customer;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Customer;
use App\Moment;

class MomentOrganisersAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $customer;
    public $moment;
    public $jeliOrganisers;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, Moment $moment, $jeliOrganisers)
    {
        $this->customer = $customer;
        $this->moment = $moment;
        $this->jeliOrganisers = $jeliOrganisers;
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
