<?php

namespace App\Models\Presenters;

use Carbon\Carbon;

trait PBase
{
    public function getDateTime($column, $format = 'Y-m-d H:i:s')
    {
        return Carbon::parse($this->$column)->format($format);
    }
}
