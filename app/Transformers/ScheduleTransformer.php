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
            'id' => $schedule->id,    
            'start_date' => Carbon::parse($schedule->start_date)->format('d-M-Y'),
            'end_date' => $schedule->end_date ? Carbon::parse($schedule->end_date)->format('d-M-Y') : null,
            'start_time' => $schedule->start_time? Carbon::parse($schedule->start_time)->format('H:i') : null,
            'end_time' => $schedule->end_time? Carbon::parse($schedule->end_time)->format('H:i') : null
        ];
    }
}
