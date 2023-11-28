<?php

namespace Core\Console\Commands;

use Illuminate\Console\Command;

class BaseCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }
}
