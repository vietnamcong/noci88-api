<?php

namespace App\Repositories;

use App\Models\SystemConfig;

class SystemConfigRepository extends CustomRepository
{
    protected $model = SystemConfig::class;

    public function __construct()
    {
        parent::__construct();
    }
}
