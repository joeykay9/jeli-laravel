<?php

namespace App\Listeners\Customer;

use App\Events\Customer\MomentOrganisersAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        //
    }
}
