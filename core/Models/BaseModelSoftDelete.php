<?php

namespace Core\Models;

use Core\Models\Concerns\BaseSoftDelete;
use Core\Models\Relations\HasRelationships;

class BaseModelSoftDelete extends BaseModel
{
    use BaseSoftDelete;
    use HasRelationships;
}
