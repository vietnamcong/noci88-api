<?php

namespace App\Models\Presenters;

trait PBetHistory
{
    public function getGameTitle()
    {
        return $this->bet_game_id;
    }

    public function getBetTime()
    {
        return $this->bet_start_time;
    }

    public function getAmount()
    {
        return moneyFormat($this->bet) . '&nbsp;' . getCurrentCurrency();
    }

    public function getResult()
    {
        switch ($this->result_bet_status) {
            case 1:
                return 'WIN';
            case 2:
                return 'LOSE';
            case 3:
                return 'DRAW';
            default:
                return '';
        }
    }

    public function getProfit()
    {
        return moneyFormat($this->turnover) . '&nbsp;' . getCurrentCurrency();
    }

    public function getPayout()
    {
        return moneyFormat($this->payout) . '&nbsp;' . getCurrentCurrency();
    }

    public function getTransactionId()
    {
        return $this->bet_id;
    }

    public function getResultTime()
    {
        return $this->bet_end_time;
    }
}
