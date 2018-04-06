<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    public function moments() {

        return $this->belongsTo(Moment::class);
    }
}
