<?php

namespace App\Models;

use App\Models\Casts\TransactionHistoryCasting;
use Illuminate\Database\Eloquent\SoftDeletes;

class BalanceTransactionHistory extends Base
{
    use SoftDeletes;
    use TransactionHistoryCasting;

    public $table = 'balance_transaction_histories';

    protected $fillable = [
        'transaction_history_id', 'transaction_type', 'amount', 'balance_before', 'balance_after'
    ];

    const TRANSACTION_TYPE_DEDUCT = 1;
    const TRANSACTION_TYPE_SETTLE = 2;
    const TRANSACTION_TYPE_CANCEL = 3;
    const TRANSACTION_TYPE_ROLLBACK = 4;

    public function transactionHistory()
    {
        return $this->belongsTo(TransactionHistory::class, 'transaction_history_id');
    }

    public function getTransactionType()
    {
        return [
            self::TRANSACTION_TYPE_DEDUCT => 'Đặt cược',
            self::TRANSACTION_TYPE_SETTLE => 'Trả thưởng',
            self::TRANSACTION_TYPE_CANCEL => 'Hủy cược',
            self::TRANSACTION_TYPE_ROLLBACK => 'Hoàn trả',
        ];
    }

    public function getTransactionTypeText()
    {
        return data_get($this->getTransactionType(), $this->transaction_type);
    }
}
