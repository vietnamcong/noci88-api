<?php

namespace App\Models\Presenters;

trait PTransactionHistory
{
    public function getGameTitle()
    {
        return getConfig('product_type.' . $this->product_type);
    }

    public function getBetTime()
    {
        return $this->transaction_time;
    }

    public function getAmount()
    {
        return moneyFormat($this->amount) . '&nbsp;' . getCurrentCurrency();
    }

    public function getResult()
    {
        switch ($this->status) {
            case 0:
                return 'WIN';
            case 1:
                return 'LOSE';
            case 3:
                return 'CANCEL';
            case 4:
                return 'RETURN_STAKE';
            case 5:
                return 'LIVE_COIN';
            case 9:
                return 'WAITING';
            default:
                return '';
        }
    }

    public function getProfit()
    {
        return $this->win_loss ? moneyFormat($this->win_loss - $this->amount) : moneyFormat(- $this->amount);
    }

    public function getPayout()
    {
        return moneyFormat($this->win_loss);
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    public function getResultTime()
    {
        return $this->result_time;
    }
}
