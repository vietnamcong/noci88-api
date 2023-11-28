<?php

namespace App\Repositories;

use App\Models\Bank;

class BankRepository extends CustomRepository
{
    protected $model = Bank::class;

    public function getListByLang()
    {
        $currentLanguage = session()->get(getConfig('language_prefix'), 'vi');
        return $this->newQuery()->where('lang', $currentLanguage)->where('is_open', getConstant('IS_OPEN.ON'))->get()->toArray();
    }
}
