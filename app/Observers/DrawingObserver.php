<?php

namespace App\Observers;

use App\Events\CheckTask;
use App\Models\Drawing;
use App\Models\Task;

class DrawingObserver
{
    // Xem xét rút tiền được phê duyệt
    public function saved(Drawing $drawing)
    {
        if ($drawing->isDirty() && in_array('status', array_keys($drawing->getDirty())) && $drawing->status == Drawing::STATUS_SUCCESS) {
            event(new CheckTask($drawing->member, Task::TYPE_SUM_DRAWING));
        }
    }
}
