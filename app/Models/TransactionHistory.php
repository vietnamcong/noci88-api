<?php

namespace App\Models;

use App\Models\Casts\TransactionHistoryCasting;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Presenters\PTransactionHistory;

class TransactionHistory extends Base
{
    use SoftDeletes;
    use TransactionHistoryCasting;
    use PTransactionHistory;

    public $table = 'transaction_histories';

    protected $fillable = [
        'member_id', 'product_type', 'game_type', 'game_id', 'game_provider', 'game_round_id', 'game_period_id',
        'transfer_code', 'transaction_id', 'amount', 'win_loss', 'transaction_time', 'result_time', 'return_stake_time',
        'rollback_time', 'cancel_time', 'order_detail', 'game_type_name', 'section', 'ip', 'status'
    ];

    // is_fs
    const IS_FS_OFF = 0;
    const IS_FS_ON = 1;

    // status
    const STATUS_WAITING = 9;
    const STATUS_WIN = 0;
    const STATUS_LOST = 1;
    const STATUS_TIE = 2;
    const STATUS_CANCEL = 3;
    const STATUS_RETURN_STAKE = 4;
    const STATUS_LIVE_COIN = 5;

    // product type
    const PT_SPORT_BOOK = 1;
    const PT_SBO_GAME = 3;
    const PT_VIRTUAL_SPORTS = 5;
    const PT_SBO_LIVE_CASINO = 7;
    const PT_SEAMLESS_GAME = 9;
    const PT_THIRD_PARTY_SPORTS_BOOK = 10;

    // game provider
    const GP_SBO = -1;
    const GP_WAN_MEI = 0;
    const GP_CQNINE = 2;
    const GP_PRAGMATIC_PLAY = 3;
    const GP_BIG_GAMING = 5;
    const GP_FLOW_GAMING = 6;
    const GP_SEXY_GAMING = 7;
    const GP_SBO_GAME = 8;
    const GP_JOKER_GAMING = 10;
    const GP_REALTIME_GAMING = 11;
    const GP_IONLC = 12;
    const GP_WORLD_MATCH = 13;
    const GP_SBO_LOTS = 14;
    const GP_FUNKY_GAMES = 16;
    const GP_IDN_LIVE = 17;
    const GP_SA_GAMING = 19;
    const GP_EVOLUTION_GAMING = 20;
    const GP_YGGDRASIL = 22;
    const GP_ALLBET = 28;
    const GP_MICRO_GAMING = 29;
    const GP_GREEN_DRAGON = 33;
    const GP_FLOW_GAMING_HUB = 36;
    const GP_PRAGMATIC_PLAY_CASINO = 38;
    const GP_TWELVE_LIVE = 39;
    const GP_GAMATRON = 41;
    const GP_SABA_SPORTS = 44;
    const GP_GIOCO_PLUS = 47;
    const GP_MPOKER = 1009;
    const GP_YGR = 1010;
    const GP_DIGITAIN = 1011;
    const GP_TC_GAMING = 1012;
    const GP_AFB_SPORTS = 1015;
    const GP_AFB_GAMING = 1016;
    const GP_YEE_BET = 1019;
    const GP_JILI_GAMING = 1020;
    const GP_BTI_SPORTS = 1022;
    const GP_NINE_GAMING = 1000;
    const GP_OG_LIVE = 1021;
    const GP_AFB_CASINO = 1024;
    const GP_PLAYTECH = 1018;
    const GP_PLAYTECH_LIVE_CASINO = 1025;

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function apiGame()
    {
        return $this->belongsTo(ApiGame::class, 'game_id', 'id');
    }

    public function balanceHistories()
    {
        return $this->hasMany(BalanceTransactionHistory::class, 'transaction_history_id');
    }

    protected $appends = ['game_title', 'bet_time', 'result'];

    public function getGameTitleAttribute() {
        return $this->getGameTitle();
    }

    public function getBetTimeAttribute() {
        return $this->getBetTime();
    }

    public function getResultAttribute() {
        return $this->getResult();
    }
}   
