<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Schedule;
use Carbon\Carbon;

class ScheduleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Schedule $schedule)
    {
        return [
            'start_date' => Carbon::parse($schedule->start_date)->format('d-M-Y'),
            'end_date' => Carbon::parse($schedule->end_date)->format('d-M-Y'),
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time
        ];
    }
}
