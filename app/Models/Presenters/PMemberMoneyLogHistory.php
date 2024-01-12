<?php

namespace App\Models\Presenters;

use App\Models\Drawing;
use App\Models\MemberMoneyLog;
use App\Models\Recharge;

trait PMemberMoneyLogHistory
{
    public function getOperateType()
    {
        $operateType = getConfig('options.operate_type.' . $this->operate_type);

        if ($this->operate_type == MemberMoneyLog::OPERATE_TYPE_MEMBER) {
            if ($this->model_name == Recharge::class) {
                $operateType = __('messages.deposit');
            }

            if ($this->model_name == Drawing::class) {
                $operateType = __('messages.withdraw');
            }
        }

        if ($this->operate_type == MemberMoneyLog::OPERATE_TYPE_DRAWING_RETURN) {
            $operateType = __('messages.withdraw');
        }

        return $operateType;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getMoneyAfter()
    {
        return moneyFormat($this->money_after) . '&nbsp;' . getCurrentCurrency();
    }

    public function getMoneyBefore()
    {
        return moneyFormat($this->money_before) . '&nbsp;' . getCurrentCurrency();
    }

    public function getMoney()
    {
        return moneyFormat($this->money) . '&nbsp;' . getCurrentCurrency();
    }

    public function getMoneyType()
    {
        $moneyType = $this->money_type;
        return data_get(getConfig('options.money_type', []), $moneyType);
    }

    public function getStatus()
    {
        $status = '';
        $class = '';

        if ($this->operate_type == MemberMoneyLog::OPERATE_TYPE_RECHARGE_ACTIVITY || $this->operate_type == MemberMoneyLog::OPERATE_TYPE_WITHDRAWAL_ACTIVITY) {
            $status = __('messages.transaction_history_page.status.waiting');
            $class = 'waiting';
        }

        if ($this->operate_type == MemberMoneyLog::OPERATE_TYPE_MEMBER) {
            $model = app($this->model_name)->where('id', $this->model_id)->first();

            if (!empty($model) && $model->status == Recharge::STATUS_UNDEAL) {
                $status = __('messages.transaction_history_page.status.waiting');
                $class = 'waiting';
            }

            if (!empty($model) && $model->status == Recharge::STATUS_SUCCESS) {
                $status = __('messages.transaction_history_page.status.success');
                $class = 'success';
            }

            if (!empty($model) && $model->status == Recharge::STATUS_FAILED) {
                $status = __('messages.transaction_history_page.status.failed');
                $class = 'failed';
            }
        }

        if ($this->operate_type == MemberMoneyLog::OPERATE_TYPE_DRAWING_RETURN) {
            $status = __('messages.transaction_history_page.status.failed');
            $class = 'failed';
        }

        return '<p class="status ' . $class . '">' . $status . '</p>';
    }
}
