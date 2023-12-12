<?php

namespace App\Http\Controllers\Api;

use App\Services\SBOService;

class SBOController extends MemberBaseController
{
    protected $service;

    public function __construct(SBOService $SBOService)
    {
        parent::__construct();
        $this->service = $SBOService;
    }

    public function getBetDetail()
    {
        return $this->service->getBetDetail(request()->all());
    }

    public function signupAccount()
    {
        return $this->service->signupAccount(request()->all());
    }

    public function updateBetSetting()
    {
        return $this->service->updateBetSetting(request()->all());
    }
}
