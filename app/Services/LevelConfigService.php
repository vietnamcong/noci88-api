<?php

namespace App\Services;

use App\Repositories\LevelConfigRepository;

class LevelConfigService extends CustomService
{
    public function __construct(LevelConfigRepository $levelConfigRepository)
    {
        $this->setRepository($levelConfigRepository);
    }
}
