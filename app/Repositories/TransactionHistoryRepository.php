<?php

namespace App\Repositories;

use App\Models\TransactionHistory;
use App\Repositories\Concerns\CustomQuery;
use Carbon\Carbon;

class TransactionHistoryRepository extends CustomRepository
{
    use CustomQuery;

    protected $model = TransactionHistory::class;

    public function __construct()
    {
        parent::__construct();
        $this->init($this->getModel()->getTable());
    }

    public function getHistories($params)
    {
        $params['member_id_eq'] = getGuard()->user()->id;
        $params['api_name_eq'] = data_get($params, 'api_name');
        $params['game_type_eq'] = data_get($params, 'game_type');
        $params['limit'] = 10;
        $params['page'] = 1;

        switch (data_get($params, 'created_at')) {
            case getConstant('OPTIONS.CREATED_AT.LAST_7_DAYS'):
                $params['created_at_gteq'] = Carbon::now()->subDays(7)->format('Y-m-d 00:00:00');
                $params['created_at_lteq'] = Carbon::now()->format('Y-m-d 23:59:59');
                break;
            case getConstant('OPTIONS.CREATED_AT.LAST_30_DAYS'):
                $params['created_at_gteq'] = Carbon::now()->subDays(30)->format('Y-m-d 00:00:00');
                $params['created_at_lteq'] = Carbon::now()->format('Y-m-d 23:59:59');
                break;
            default:
                $params['created_at_gteq'] = Carbon::now()->format('Y-m-d 00:00:00');
                $params['created_at_lteq'] = Carbon::now()->format('Y-m-d 23:59:59');
                break;
        }
        return $this->search($params)->get();
    }
}
