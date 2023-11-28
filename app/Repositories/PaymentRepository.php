<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository extends CustomRepository
{
    protected $model = Payment::class;

    public function __construct()
    {
        parent::__construct();
    }
}
