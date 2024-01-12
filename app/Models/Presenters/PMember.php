<?php

namespace App\Models\Presenters;

use App\Models\LevelConfig;

trait PMember
{
    public function getCurrentLevel($levels)
    {
        $currentLevel = app(LevelConfig::class);
        foreach ($levels as $item) {
            if ($this->level == $item->level) {
                $currentLevel = $item;
            }
        }
        return $currentLevel;
    }

    public function getNextLevel($levels)
    {
        $nextLevel = app(LevelConfig::class);
        foreach ($levels as $item) {
            if ($this->level + 1 == $item->level) {
                $nextLevel = $item;
            }
        }
        return $nextLevel;
    }
}
