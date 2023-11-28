<?php

namespace App\Repositories;

use App\Models\LevelConfig;

class LevelConfigRepository extends CustomRepository
{
    protected $model = LevelConfig::class;

    public function __construct()
    {
        parent::__construct();
    }
}
