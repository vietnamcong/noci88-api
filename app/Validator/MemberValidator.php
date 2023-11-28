<?php

namespace App\Validator;

use App\Models\Member;
use App\Models\SystemConfig;
use Illuminate\Support\Str;

class MemberValidator extends CustomValidator
{
    protected $_model = Member::class;

    public function validateLogin($params)
    {
        $rules = [
            'name' => 'required',
            'password' => 'required'
        ];

        return $this->_addRulesMessages($rules, [], false)
            ->with($params)
            ->passes();
    }

    public function validateSignup($params)
    {
        $rules = [
            'name' => 'required|min:6|max:20|unique_in_sensitive:members,name',
            'password' => 'required|min:6',
            'password_confirm' => 'required_with:password|same:password',
            'realname' => 'required|max:50',
            'qk_pwd' => 'required|number|min:6',
            'phone' => 'required|number|min:10',
            'lang' => 'required',
            'captcha' => 'required|captcha',
        ];

        return $this->_addRulesMessages($rules, $this->getSignupMessages(), false)
            ->with($params)
            ->passes();
    }

    public function validateChangePassword($params)
    {
        $rules = [
            'oldpassword' => 'required_with:password,password_confirmation',
            'password' => 'required_with:password_confirmation|nullable|min:6',
            'password_confirmation' => 'required_with:password|same:password'
        ];

        return $this->_addRulesMessages($rules, $this->getPasswordMessages(), false)
            ->with($params)
            ->passes();
    }

    public function validateRegisterAgent($params)
    {
        $rules = [
            'name' => 'bail|required|max:50|unique:members,name',
            'password' => 'bail|required|min:6',
            'realname' => 'bail|required|max:50',
            'phone' => 'bail|required|number|min:10|unique:members,phone',
            'captcha' => 'required|captcha'
        ];

        return $this->_addRulesMessages($rules, $this->getRegisterAgentMessages(), false)
            ->with($params)
            ->passes();
    }

    protected function getPasswordMessages()
    {
        $messages = [];

        foreach (request()->all() as $item) {
            $messages += [
                'oldpassword.required_with' => __('validation.old_password_required'),
                'password.required_with' => __('validation.password_required'),
                'password.min' => __('validation.password_min'),
                'password_confirmation.required_with' => __('validation.password_confirm_required'),
                'password_confirmation.same' => __('validation.password_confirm_invalid'),
            ];
        }

        return $messages;
    }

    protected function getRegisterAgentMessages()
    {
        $messages = [];

        foreach (request()->all() as $item) {
            $messages += [
                'name.required' => __('validation.agent_name_required'),
                'name.unique' => __('validation.agent_name_unique'),
                'name.max' => __('validation.name_max', ['max' => 50]),
                'password.required' => __('validation.password_required'),
                'password.min' => __('validation.password_min', ['min' => 6]),
                'realname.required' => __('validation.realname_required'),
                'realname.max' => __('validation.realname_max', ['max' => 50]),
                'captcha.required' => __('validation.captcha_required'),
                'captcha.captcha' => __('validation.captcha_required')
            ];
        }

        return $messages;
    }

    protected function getSignupMessages()
    {
        $messages = [];

        foreach (request()->all() as $item) {
            $messages += [
                'name.min' => __('validation.name_min'),
                'name.max' => __('validation.name_max'),
                'name.unique_in_sensitive' => __('validation.name_unique'),
                'password.min' => __('validation.password_min'),
                'password_confirm.required_with' => __('validation.password_confirm_required'),
                'password_confirm.same' => __('validation.password_confirm_invalid'),
                'qk_pwd.required' => __('validation.withdraw_password_required'),
                'qk_pwd.min' => __('validation.withdraw_password_min'),
                'qk_pwd.number' => __('validation.qk_pwd_number'),
                'phone.number' => __('validation.phone_invalid'),
                'lang.required' => __('validation.lang_required'),
                'captcha.captcha' => __('validation.captcha_invalid'),
            ];
        }

        return $messages;
    }

    public function validateMemberBank()
    {
        $params = request()->all();
        $rules = [
            'bank_type' => 'bail|required',
            'owner_name' => 'bail|required|max:255',
            'card_no' => 'bail|required|digits_between:6,20|unique:member_banks,card_no,' . request('id'),
            'bank_address' => 'bail|required|max:255',
        ];

        return $this->_addRulesMessages($rules, [], false)
            ->with($params)
            ->passes();
    }

    public function validateWithdraw($minAmount, $maxAmount)
    {
        $requestParams = request()->all();
        $withdrawalAmount = data_get($requestParams, 'money');
        $withdrawalAmount = Str::replace(',', '', $withdrawalAmount);
        $requestParams['money'] = $withdrawalAmount;

        $rules = [
            'member_bank_id' => 'bail|required',
            'money' => 'bail|required|numeric|min:' . $minAmount . '|max:' . $maxAmount,
            'qk_pwd' => 'bail|required|in:' . getGuard()->user()->qk_pwd,
        ];

        $messages = [
            'money.numeric' => __('validation.withdrawal_invalid'),
            'money.min' => __('validation.withdrawal_amount_min', ['min' => moneyFormat($minAmount) . ' ' . getCurrentCurrency()]),
            'money.max' => __('validation.withdrawal_amount_max', ['max' => moneyFormat($maxAmount) . ' ' . getCurrentCurrency()]),
            'qk_pwd.in' => __('validation.qk_pwd_invalid'),
        ];

        return $this->_addRulesMessages($rules, $messages, false)
            ->with($requestParams)
            ->passes();
    }
}
