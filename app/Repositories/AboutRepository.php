<?php

namespace App\Repositories;

use App\Models\About;

class AboutRepository extends CustomRepository
{
    protected $model = About::class;

    public function __construct()
    {
        parent::__construct();
    }
}
