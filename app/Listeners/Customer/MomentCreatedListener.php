<?php

namespace App\Listeners\Customer;

use App\Events\Customer\MomentCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class MomentCreatedListener implements ShouldQueue
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
     * @param  MomentCreated  $event
     * @return void
     */
    public function handle(MomentCreated $event)
    {
        Log::channel('slack')->info('New Jeli Moment Created', [
            'Creator' => $event->moment->creator->first_name ? $event->moment->creator->first_name . ' ' . $event->moment->creator->last_name : $event->moment->creator->jelion,
            'Category' => $event->moment->category,
            'Title' => $event->moment->title,
            'Place' => $event->moment->place()->first()->place_name,
        ]);
    }
}
