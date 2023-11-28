<?php

namespace Core\Models\Pivots;

use Core\Models\Concerns\BaseSoftDelete;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BasePivot extends Pivot
{
    use BaseSoftDelete;
}
