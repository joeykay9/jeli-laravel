<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Schedule;

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
            'start_date' => $schedule->start_date,
            'end_date' => $schedule->end_date,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time
        ];
    }
}
