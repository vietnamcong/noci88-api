<?php

namespace App\Http\Controllers\Api;

use App\Models\BonusHistory;
use App\Models\SystemConfig;
use App\Models\TransactionHistory;
use App\Models\Member;
use App\Models\TipHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Seamless Wallet Flow
 *
 * Class SeamlessController
 * @package App\Http\Controllers\Api
 */
class SeamlessController extends MemberBaseController
{
    protected $params = null;

    protected $member = null;

    protected $transactionHistoryModel = null;

    protected $tipHistoryModel = null;

    protected $bonusHistoryModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->setParams(request()->all());
        $this->transactionHistoryModel = app(TransactionHistory::class);
        $this->tipHistoryModel = app(TipHistory::class);
        $this->bonusHistoryModel = app(BonusHistory::class);
    }

    public function setParams($params = [])
    {
        $this->params = $params;
    }

    public function getParam($key = null, $default = null)
    {
        return data_get($this->params, $key, $default);
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get member's balance
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getBalance()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // return member's balance
        $res = [
            'AccountName' => $this->getParam('Username'),
            'Balance' => $this->member->money,
            'ErrorCode' => getConst('ERROR_CODE.NO_ERROR'),
            'ErrorMessage' => "No Error"
        ];

        $this->logResponse('info', $res);

        return $this->respond($res);
    }

    /**
     * Bet process
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function deduct()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // check balance
        if (blank($this->member->money) || $this->member->money < $this->getParam('Amount')) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.NOT_ENOUGH_BALANCE'),
                'ErrorMessage' => 'Not enough balance'
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        // get history from db
        $conditions = [];
        $history = $this->getHistories($conditions);

        if (!blank($history)) {
            // check status
            // Sport game & Virtual Sport game can't deduct twice
            // 3rd Wan Mei (Seamless game provider) can deduct twice but with different transactionId
            $checkStatus = $this->getParam('TransactionId') == $history->transaction_id && in_array($history->status, [TransactionHistory::STATUS_WIN, TransactionHistory::STATUS_LOST, TransactionHistory::STATUS_TIE, TransactionHistory::STATUS_CANCEL]);
            $checkSportGame = in_array($this->getParam('ProductType'), [TransactionHistory::PT_SPORT_BOOK, TransactionHistory::PT_VIRTUAL_SPORTS]);
            $checkSeamlessGame = $this->getParam('ProductType') == TransactionHistory::PT_SEAMLESS_GAME && $this->getParam('TransactionId') == $history->transaction_id;

            if ($checkStatus || $checkSportGame || $checkSeamlessGame) {
                $res = [
                    'AccountName' => $this->getParam('Username'),
                    'Balance' => 0,
                    'ErrorCode' => getConst('ERROR_CODE.BET_REFNO_EXIST'),
                    'ErrorMessage' => "Bet With Same RefNo Exists"
                ];

                $this->logResponse('error', $res);

                return $this->respond($res);
            }

            // Casino and RNG Game (SBO Game) can deduct twice but 2nd deduct amount must be greater than 1st deduct
            if (in_array($this->getParam('ProductType'), [TransactionHistory::PT_SBO_GAME, TransactionHistory::PT_SBO_LIVE_CASINO]) && $this->getParam('Amount') < $history->amount) {
                $res = [
                    'AccountName' => $this->getParam('Username'),
                    'Balance' => 0,
                    'ErrorCode' => getConst('ERROR_CODE.INTERNAL_ERROR'),
                    'ErrorMessage' => "Internal Error"
                ];

                $this->logResponse('error', $res);

                return $this->respond($res);
            }
        }

        // if not exist old history, it'll make new history
        if (blank($history) || $this->getParam('TransactionId') != $history->transaction_id) {
            $history = $this->transactionHistoryModel;
        }

        DB::beginTransaction();
        try {
            // bet process
            $diffAmount = $this->getParam('Amount');
            if ($history->id) {
                $diffAmount = $this->getParam('Amount') - $history->amount;
            }

            $history->member_id = $this->member->id;
            $history->product_type = $this->getParam('ProductType');
            $history->game_type = $this->getParam('GameType');
            $history->game_id = $this->getParam('GameId');
            $history->game_provider = $this->getParam('Gpid');
            $history->game_round_id = $this->getParam('GameRoundId');
            $history->game_period_id = $this->getParam('GamePeriodId');
            $history->transfer_code = $this->getParam('TransferCode');
            $history->transaction_id = $this->getParam('TransactionId');
            $history->amount = $this->getParam('Amount');
            $history->transaction_time = $this->parseTime($this->getParam('BetTime'));
            $history->order_detail = $this->getParam('OrderDetail');
            $history->game_type_name = $this->getParam('GameTypeName');
            $history->ip = $this->getParam('PlayerIp', request()->ip());
            $history->status = TransactionHistory::STATUS_WAITING;
            $history->save();

            // update db and response
            $this->member->money = $this->member->money - $diffAmount;
            //$this->member->save();

            DB::table(app(Member::class)->getTable())
                ->where('id', $this->member->id)
                ->update([
                    'money' => DB::raw('money - ' . $diffAmount),
                ]);

            DB::commit();

            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => $this->member->money,
                'ErrorCode' => 0,
                'ErrorMessage' => "No Error",
                "BetAmount" => $this->getParam('Amount')
            ];

            $this->logResponse('info', $res);

            return $this->respond($res);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
        }

        return $this->error();
    }

    /**
     * Update member's balance after bet
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function settle()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // check histories
        $histories = $this->getHistories(['getAll' => true]);
        if (blank($histories)) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_NOT_EXIST'),
                'ErrorMessage' => "Bet not exists"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        $member = Member::where('id', $this->member->id)->first();

        $balanceBefore = $member->money;
        $balanceAfter = $balanceBefore + $this->getParam('WinLoss');

        DB::beginTransaction();
        try {
            // settle process
            $checkCancel = $checkSettle = false;
            $hasMultiHistories = $histories->count() > 1;

            foreach ($histories as $history) {
                // check settle status
                if ($history->status == TransactionHistory::STATUS_WIN || $history->status == TransactionHistory::STATUS_LOST || $history->status == TransactionHistory::STATUS_TIE) {
                    if (!$hasMultiHistories || $checkSettle) {
                        $res = [
                            'AccountName' => $this->getParam('Username'),
                            'Balance' => 0,
                            'ErrorCode' => getConst('ERROR_CODE.BET_ALREADY_SETTLED'),
                            'ErrorMessage' => "Bet Already Settled"
                        ];

                        $this->logResponse('error', $res);

                        DB::rollBack();

                        return $this->respond($res);
                    } else {
                        $checkSettle = true;
                        continue;
                    }
                }

                // check cancel status
                if ($history->status == TransactionHistory::STATUS_CANCEL) {
                    if (!$hasMultiHistories || $checkCancel) {
                        $res = [
                            'AccountName' => $this->getParam('Username'),
                            'Balance' => 0,
                            'ErrorCode' => getConst('ERROR_CODE.BET_ALREADY_CANCELED'),
                            'ErrorMessage' => "Bet Already Canceled"
                        ];

                        $this->logResponse('error', $res);

                        DB::rollBack();

                        return $this->respond($res);
                    } else {
                        $checkCancel = true;
                        continue;
                    }
                }

                $history->status = $this->getParam('ResultType');
                $history->win_loss = $this->getParam('WinLoss');
                $history->balance_before = $balanceBefore;
                $history->balance_after = $balanceAfter;
                $history->result_time = $this->getParam('ResultTime');

                $history->save();
            }

            // update db and response
            $member->money = $balanceAfter;
            //$member->save();

            DB::table(app(Member::class)->getTable())
                ->where('id', $this->member->id)
                ->update([
                    'money' => DB::raw('money + ' . $this->getParam('WinLoss')),
                ]);

            DB::commit();

            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => $balanceAfter,
                'ErrorCode' => 0,
                'ErrorMessage' => "No Error"
            ];

            $this->logResponse('info', $res);

            return $this->respond($res);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
        }

        return $this->error();
    }

    /**
     * Rollback bet
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function rollback()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // check bet history exist
        $histories = $this->getHistories(['getAll' => true]);
        if (blank($histories)) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_NOT_EXIST'),
                'ErrorMessage' => "Bet not exists"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        DB::beginTransaction();
        try {
            // rollback process
            $stake = 0;
            foreach ($histories as $history) {
                if ($history->status == TransactionHistory::STATUS_CANCEL) {
                    $stake += $history->amount;
                }

                if (in_array($history->status, [TransactionHistory::STATUS_WIN, TransactionHistory::STATUS_LOST, TransactionHistory::STATUS_TIE])) {
                    $stake = $history->win_loss;
                }

                if (!blank($history->rollback_time)) {
                    $res = [
                        'AccountName' => $this->getParam('Username'),
                        'Balance' => 0,
                        'ErrorCode' => getConst('ERROR_CODE.BET_ALREADY_ROLLBACK'),
                        'ErrorMessage' => "Bet Already Rollback"
                    ];

                    $this->logResponse('error', $res);

                    DB::rollBack();

                    return $this->respond($res);
                }

                $history->win_loss = null;
                $history->status = TransactionHistory::STATUS_WAITING;
                $history->rollback_time = Carbon::now();
                $history->save();
            }

            // update db and response
            $this->member->money = $this->member->money - $stake;
            $this->member->save();

            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => $this->member->money,
                'ErrorCode' => 0,
                'ErrorMessage' => "No Error"
            ];

            $this->logResponse('info', $res);

            DB::commit();

            return $this->respond($res);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
        }

        return $this->error();
    }

    /**
     * Cancel bet
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function cancel()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // check bet history exist
        $histories = $this->getHistories(['getAll' => true]);
        if (blank($histories)) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_NOT_EXIST'),
                'ErrorMessage' => "Bet not exists"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        DB::beginTransaction();
        try {
            // cancel process
            $diffAmount = $winLoss = 0;
            $transactionId = $this->getParam('TransactionId');

            foreach ($histories as $history) {
                // already canceled
                if ($this->getParam('IsCancelAll') && $history->transaction_id == $transactionId && $history->status == TransactionHistory::STATUS_CANCEL) {
                    $res = [
                        'AccountName' => $this->getParam('Username'),
                        'Balance' => 0,
                        'ErrorCode' => getConst('ERROR_CODE.BET_ALREADY_CANCELED'),
                        'ErrorMessage' => "Bet Already Canceled"
                    ];

                    $this->logResponse('error', $res);

                    DB::rollBack();

                    return $this->respond($res);
                }

                $winLoss = $history->win_loss;

                // check if not cancel all
                if (!$this->getParam('IsCancelAll')) {
                    if ($history->transaction_id != $transactionId) {
                        continue;
                    }

                    if ($history->status == TransactionHistory::STATUS_CANCEL) {
                        $res = [
                            'AccountName' => $this->getParam('Username'),
                            'Balance' => 0,
                            'ErrorCode' => getConst('ERROR_CODE.BET_ALREADY_CANCELED'),
                            'ErrorMessage' => "Bet Already Canceled"
                        ];

                        $this->logResponse('error', $res);

                        DB::rollBack();

                        return $this->respond($res);
                    }

                    $diffAmount = $history->amount;
                    $history->status = TransactionHistory::STATUS_CANCEL;
                    $history->cancel_time = Carbon::now();
                    $history->save();
                    break;
                }

                $diffAmount += $history->amount;
                $history->status = TransactionHistory::STATUS_CANCEL;
                $history->cancel_time = Carbon::now();
                $history->save();
            }

            // update db and response
            $this->member->money = $this->member->money + $diffAmount - $winLoss;
            $this->member->save();

            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => $this->member->money,
                'ErrorCode' => 0,
                'ErrorMessage' => "No Error"
            ];

            $this->logResponse('info', $res);

            DB::commit();

            return $this->respond($res);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
        }

        return $this->error();
    }

    /**
     * Tip process
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function tip()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // check balance
        if (blank($this->member->money) || $this->member->money < $this->getParam('Amount')) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.NOT_ENOUGH_BALANCE'),
                'ErrorMessage' => "Not enough balance"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        DB::beginTransaction();
        try {
            // tip process
            $history = $this->tipHistoryModel;
            $history->member_id = $this->member->id;
            $history->product_type = $this->getParam('ProductType');
            $history->game_type = $this->getParam('GameType');
            $history->game_provider = $this->getParam('Gpid');
            $history->transfer_code = $this->getParam('TransferCode');
            $history->transaction_id = $this->getParam('TransactionId');
            $history->amount = $this->getParam('Amount');
            $history->tip_time = $this->parseTime($this->getParam('TipTime'));
            $history->save();

            // update db and response
            $this->member->money = $this->member->money - $this->getParam('Amount');
            $this->member->save();

            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => $this->member->money,
                'ErrorCode' => 0,
                'ErrorMessage' => "No Error"
            ];

            $this->logResponse('info', $res);

            DB::commit();

            return $this->respond($res);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
        }

        return $this->error();
    }

    /**
     * Bonus process
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function bonus()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // check bonus exist
        $bonusHistory = $this->bonusHistoryModel
            ->where('member_id', $this->member->id)
            ->where('transfer_code', $this->getParam('TransferCode'))
            ->where('product_type', $this->getParam('ProductType'))
            ->where('game_type', $this->getParam('GameType'))
            ->where('game_id', $this->getParam('GameId'))
            ->first();

        if (!blank($bonusHistory)) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_REFNO_EXIST'),
                'ErrorMessage' => "Bet With Same RefNo Exists"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        DB::beginTransaction();
        try {
            // bonus process
            $history = $this->bonusHistoryModel;
            $history->member_id = $this->member->id;
            $history->product_type = $this->getParam('ProductType');
            $history->game_type = $this->getParam('GameType');
            $history->game_id = $this->getParam('GameId');
            $history->game_provider = $this->getParam('Gpid');
            $history->transfer_code = $this->getParam('TransferCode');
            $history->transaction_id = $this->getParam('TransactionId');
            $history->amount = $this->getParam('Amount');
            $history->bonus_time = $this->parseTime($this->getParam('BonusTime'));
            $history->save();

            // update db and response
            $this->member->money = $this->member->money + $this->getParam('Amount');
            $this->member->save();

            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => $this->member->money,
                'ErrorCode' => 0,
                'ErrorMessage' => "No Error"
            ];

            $this->logResponse('info', $res);

            DB::commit();

            return $this->respond($res);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
        }

        return $this->error();
    }

    /**
     * Return stake
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function returnStake()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // check bet history exist
        $history = $this->getHistories(['transaction_id' => $this->getParam('TransactionId')]);
        if (blank($history)) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_NOT_EXIST'),
                'ErrorMessage' => "Bet not exists"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        if ($history->status == TransactionHistory::STATUS_RETURN_STAKE) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_ALREADY_RETURNED_STAKE'),
                'ErrorMessage' => "Bet Already Returned Stake"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        if ($history->status == TransactionHistory::STATUS_CANCEL) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_REFNO_EXIST'),
                'ErrorMessage' => "Bet With Same RefNo Exists"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        DB::beginTransaction();
        try {
            $diffBalance = $history->amount - request()->get('CurrentStake');

            // update db and response
            $history->amount = request()->get('CurrentStake');
            $history->return_stake_time = $this->parseTime(request()->get('ReturnStakeTime'));
            $history->status = TransactionHistory::STATUS_RETURN_STAKE;
            $history->save();

            $this->member->money = $this->member->money + $diffBalance;
            $this->member->save();

            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => $this->member->money,
                'ErrorCode' => 0,
                'ErrorMessage' => "No Error"
            ];

            $this->logResponse('info', $res);

            DB::commit();

            return $this->respond($res);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
        }

        return $this->error();
    }

    /**
     * LiveCoin transaction
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function liveCoinTransaction()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // check balance
        if (blank($this->member->money) || $this->member->money < $this->getParam('Amount')) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.NOT_ENOUGH_BALANCE'),
                'ErrorMessage' => "Not enough balance"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        // get history from db
        $history = $this->getHistories();
        if (!blank($history)) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_REFNO_EXIST'),
                'ErrorMessage' => "Bet With Same RefNo Exists"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        if (blank($history)) {
            $history = $this->transactionHistoryModel;
        }

        DB::beginTransaction();
        try {
            // buy livecoin process
            $history->member_id = $this->member->id;
            $history->product_type = $this->getParam('ProductType');
            $history->game_type = $this->getParam('GameType');
            $history->transfer_code = $this->getParam('TransferCode');
            $history->transaction_id = $this->getParam('TransactionId');
            $history->amount = $this->getParam('Amount');
            $history->transaction_time = $this->parseTime($this->getParam('TransactionTime'));
            $history->section = $this->getParam('Selection');
            $history->status = TransactionHistory::STATUS_LIVE_COIN;
            $history->save();

            // update db and response
            $this->member->money = $this->member->money - $this->getParam('Amount');
            $this->member->save();

            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => $this->member->money,
                'ErrorCode' => 0,
                'ErrorMessage' => "No Error",
            ];

            $this->logResponse('info', $res);

            DB::commit();

            return $this->respond($res);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
        }

        return $this->error();
    }

    /**
     * Get bet status
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getBetStatus()
    {
        $this->logRequest();

        // validate params
        $valid = $this->validateUser();
        if ($valid !== true) {
            $this->logResponse('error', $valid);

            return $this->respond($valid);
        }

        // get history from db
        $history = $this->getHistories(['transaction_id' => $this->getParam('TransactionId')]);
        if (blank($history)) {
            $res = [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.BET_NOT_EXIST'),
                'ErrorMessage' => "Bet not exists"
            ];

            $this->logResponse('error', $res);

            return $this->respond($res);
        }

        $status = 'void';

        if ($history->status == TransactionHistory::STATUS_WAITING) {
            $status = 'running';
        }

        if (in_array($history->status, [TransactionHistory::STATUS_WIN, TransactionHistory::STATUS_LOST])) {
            $status = 'settled';
        }

        $res = [
            'TransferCode' => $history->transfer_code,
            'TransactionId' => $history->transaction_id,
            'Status' => $status,
            'WinLoss' => $history->win_loss,
            'Stake' => $history->amount,
            'ErrorCode' => 0,
            'ErrorMessage' => "No Error"
        ];

        $this->logResponse('info', $res);

        return $this->respond($res);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error()
    {
        $res = [
            'AccountName' => $this->getParam('Username'),
            'Balance' => 0,
            'ErrorCode' => getConst('ERROR_CODE.INTERNAL_ERROR'),
            'ErrorMessage' => "Internal Error"
        ];

        $this->logResponse('error', $res);

        return $this->respond($res);
    }

    /**
     * @return array|bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function validateUser()
    {
        // validate username
        if (blank(request()->get('Username'))) {
            return [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.USERNAME_EMPTY'),
                'ErrorMessage' => "Username empty"
            ];
        }

        // validate company key
        if (blank($this->getParam('CompanyKey'))) {
            return [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.COMPANY_KEY_ERROR'),
                'ErrorMessage' => "CompanyKey Error"
            ];
        }
        $config = SystemConfig::where('name', 'company_key')->first();
        if (blank($config) || $this->getParam('CompanyKey') != data_get($config, 'value')) {
            return [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.COMPANY_KEY_ERROR'),
                'ErrorMessage' => "CompanyKey Error"
            ];
        }

        // check member exist
        $member = Member::where('name', request()->get('Username'))->first();

        if (blank($member)) {
            return [
                'AccountName' => $this->getParam('Username'),
                'Balance' => 0,
                'ErrorCode' => getConst('ERROR_CODE.MEMBER_NOT_EXIST'),
                'ErrorMessage' => "Member not exist"
            ];
        }

        $this->member = $member;

        return true;
    }

    /**
     * @param array $conditions
     * @return mixed
     */
    protected function getHistories(array $conditions = [])
    {
        $transaction = $this->transactionHistoryModel
            ->where('member_id', $this->member->id)
            ->where('transfer_code', $this->getParam('TransferCode'))
            ->where('product_type', $this->getParam('ProductType'));

        if (in_array($this->getParam('ProductType'), [TransactionHistory::PT_SPORT_BOOK, TransactionHistory::PT_SBO_GAME, TransactionHistory::PT_SBO_LIVE_CASINO])) {
            $transaction->where('game_type', $this->getParam('GameType'));
        }

        if (!blank($conditions)) {
            foreach ($conditions as $field => $value) {
                if ($field == 'getAll') {
                    continue;
                }

                $transaction->where($field, $value);
            }
        }

        return data_get($conditions, 'getAll') ? $transaction->get() : $transaction->first();
    }

    /**
     * @param string $time
     * @return Carbon|null
     */
    protected function parseTime(string $time)
    {
        try {
            return Carbon::parse($time);
        } catch (\Exception $exception) {
        }
        return null;
    }

    /**
     * Check log enabled/disabled
     *
     * @return bool
     */
    protected function isEnableLog(): bool
    {
        $systemConfig = SystemConfig::where('name', 'enable_sbo_log')
            ->where('is_open', 1)
            ->first();

        if ($systemConfig) {
            return (bool)$systemConfig->value;
        }

        return false;
    }

    /**
     * Log request parameters
     *
     * @return void
     */
    protected function logRequest()
    {
        if (!$this->isEnableLog()) {
            return;
        }

        $log = [
            'Request To' => request()->getUri(),
            'Request' => $this->getParams(),
            'IP' => request()->ip(),
            'Agent' => request()->userAgent(),
        ];

        Log::info(json_encode($log));
    }

    /**
     * Log response
     *
     * @param string $type
     * @param array $response
     * @return void
     */
    protected function logResponse($type = 'info', $response = [])
    {
        if (!$this->isEnableLog()) {
            return;
        }

        $log = [
            'Response From' => request()->getUri(),
            'Response' => $response ?? null,
        ];

        Log::{$type}(json_encode($log));
    }
}
