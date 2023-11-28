<?php

namespace App\Validator;

use App\Models\CreditPayRecord;

class CreditValidator extends CustomValidator
{
    protected $_model = CreditPayRecord::class;

    public function validateBorrowValid($params)
    {
        $rules = [
            'name' => 'required',
            'realname' => 'required',
            'money' => 'required|min:0|numeric',
            'days' => 'required|integer|min:0|max:' . CreditPayRecord::CREDIT_PAY_DAYS
        ];

        return $this->_addRulesMessages($rules, [], false)
            ->with($params)
            ->passes();
    }

    public function validateCreditCheckBalance($params)
    {
        $rules = [
            'name' => 'required',
        ];

        return $this->_addRulesMessages($rules, [], false)
            ->with($params)
            ->passes();
    }

    public function validateCreditLend($params)
    {
        $rules = [
            'account' => 'required',
            'realname' => 'required',
            'money' => 'required|min:0|numeric'
        ];

        return $this->_addRulesMessages($rules, [], false)
            ->with($params)
            ->passes();
    }

    public function validateCreditHistory($params)
    {
        $rules = [
            'name' => 'required',
        ];

        return $this->_addRulesMessages($rules, [], false)
            ->with($params)
            ->passes();
    }
}
