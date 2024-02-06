<?php

namespace App\Http\Controllers\Api;

use App\Models\EeziepayHistory;
use App\Models\SystemConfig;
use App\Models\Member;
use App\Models\MemberMoneyLog;
use App\Models\Recharge;
use App\Models\Payment;
use App\Repositories\SystemConfigRepository;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


class EeziepayController extends Controller
{
    protected $systemConfigRepository;

    public function __construct()
    {
        $this->systemConfigRepository = app(SystemConfigRepository::class);
    }

    public function deposit()
    {
        // save session params
        session()->put('eeziepay_deposit', request()->all());

        return redirect()->to(route('frontend.eeziepay.deposit.confirm'));
    }

    public function confirm(Request $request)
    {
        // get params config
        $systemConfigs = $this->systemConfigRepository
            ->where('config_group', SystemConfig::REMOTE_API)
            ->where('lang', SystemConfig::LANG_COMMON)
            ->get()
            ->pluck('value', 'name')
            ->toArray();

        $paymentAmount = $request->get('payment_amount');
        $bankCode = $request->get('bank_code');

        $params = [
            'service_version' => getConfig('eeziepay.service_version'),
            'partner_code' => data_get($systemConfigs, 'eeziepay_code'),
            'partner_orderid' => $this->makeTransactionId(),
            'member_id' => getGuard()->user()->name,
            'member_ip' => request()->ip(),
            'currency' => 'VND',
            'amount' => eeziepayMoneyConvert($paymentAmount),
            'backend_url' => route('eeziepay.callback'),
            'redirect_url' => route('eeziepay.redirect'),
            'bank_code' => $bankCode,
            'trans_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'key' => data_get($systemConfigs, 'eeziepay_key'),
        ];
        
        $eeziepayHistory = app(EeziepayHistory::class);
        $eeziepayHistory->fill([
            'member_id' => getGuard()->user()->id,
            'billno' => '',
            'partner_orderid' => data_get($params, 'partner_orderid'),
            'bank_code' => data_get($params, 'bank_code'),
            'currency' => data_get($params, 'currency'),
            'request_amount' => data_get($params, 'amount') / 100,
            'status' => EeziepayHistory::STATUS_WAITING,
        ])->save();


        // make Eeziepay signature
        $signature = '';
        foreach ($params as $key => $value) {
            $signature .= $key . '=' . $value . '&';
        }
        $sign = strtoupper(sha1(trim($signature, '&')));
        $params['remarks'] = getConfig('eeziepay.remarks_prefix');
        $params['sign'] = $sign;
        $params['eeziepay_fundtransfer_url'] = data_get($systemConfigs, 'eeziepay_fundtransfer');
        unset($params['key']);

        return $this->success(['data' => $params]);
    }

    public function depositValid()
    {
        $params = request()->all();

        /**
         * Update Eeziepay history
         * @var EeziepayHistory $eeziepayHistory
         */
        $eeziepayHistory = app(EeziepayHistory::class);
        $eeziepayHistory->fill([
            'member_id' => getGuard()->user()->id,
            'billno' => '',
            'partner_orderid' => data_get($params, 'partner_orderid'),
            'bank_code' => data_get($params, 'bank_code'),
            'currency' => data_get($params, 'currency'),
            'request_amount' => data_get($params, 'amount') / 100,
            'receive_amount' => '',
            'fee' => '',
            'status' => EeziepayHistory::STATUS_WAITING,
        ])->save();

        // clear session
        session()->forget('eeziepay_deposit');

        return redirect()->to(route('frontend.mypage.transaction_histories'));
    }

    public function redirect()
    {
        return view('pay-successful');
    }


    public function histories()
    {
        // get params config
        $systemConfigs = $this->systemConfigRepository
            ->where('config_group', SystemConfig::REMOTE_API)
            ->where('lang', SystemConfig::LANG_COMMON)
            ->get()
            ->pluck('value', 'name')
            ->toArray();

        $params = [
            'service_version' => getConfig('eeziepay.service_version'),
            'partner_code' => data_get($systemConfigs, 'eeziepay_code'),
            'partner_orderid' => request()->get('partner_orderid'),
            'currency' => 'VND',
            'key' => data_get($systemConfigs, 'eeziepay_key'),
        ];

        // make Eeziepay signature
        $signature = '';
        foreach ($params as $key => $value) {
            $signature .= $key . '=' . $value . '&';
        }

        $sign = strtoupper(sha1(trim($signature, '&')));
        $params['sign'] = $sign;

        $res = app(Client::class)->request('POST', 'https://gogomart168com.com/fundtransfer_enquiry.php', $params);
        dd($res);
    }

    protected function makeTransactionId(): string
    {
        $transactionId = Str::upper($this->randomString(4)) . Carbon::now()->format('YmdHis');

        if (EeziepayHistory::where('partner_orderid', $transactionId)->exists()) {
            return $this->makeTransactionId();
        }

        return $transactionId;
    }

    public function callback()
    {
        // check history
        $history = app(EeziepayHistory::class)->where('partner_orderid', request()->get('partner_orderid'))->first();

        if (empty($history)) {
            logInfo(request()->all());
            return $this->xml();
        }
        
        // get member info
        $member = app(Member::class)->where('id', $history->member_id)->first();
        
        DB::beginTransaction();
        try {

            $amount = request()->get('receive_amount') / 100;
            $status = request()->get('status');

            // update history
            $historyId = $history->id;
            $history->billno = request()->get('billno');
            $history->receive_amount = $amount;
            $history->fee = request()->get('fee') / 100;
            $history->status = $status;
            $history->transaction_at = Carbon::now()->timezone(config('app.timezone'))->toDateTimeString();
            $history->save();

            // send message to telegram group
            $message = "[YÊU CẦU NẠP TIỀN] - tài khoản: " . $member->name
                . " [Nạp tiền]: " . $member->name
                . " - Từ: EeziePay - Mã ngân hàng: " . $history->bank_code
                . " - số tiền: " . $history->receive_amount;

            app(ActivityService::class)->sendAlertTelegram($message);

            // check success status
            if ($status == EeziepayHistory::STATUS_BANK_SUCCESS) {
                // update member
                $member = app(Member::class)->find($history->member_id);
                $beforeMoney = $member->money;
                $member->money = $member->money + moneyConvert($amount);
                $member->save();

                $recharge = Recharge::create([
                    'bill_no'           => request()->get('billno'),
                    'name'              => $member->name,
                    'member_id'         => $member->id,
                    'account'           => '',
                    'origin_money'      => 0,
                    'money'             => $amount / 1000,
                    'money_before'      => $beforeMoney,
                    'money_after'       => $member->money,
                    'payment_type'      => Payment::PAYMENT_EEZIEPAY,
                    'payment_detail'    => "",
                    'status'            => 2,
                    'lang'              => $member->lang,
                    'hk_at'             => Carbon::now()->format('Y-m-d H:i:s')
                ]);
                
                // update money log
                MemberMoneyLog::create([
                    'member_id'         => $history->member_id,
                    'money'             => $history->receive_amount,
                    'money_before'      => $beforeMoney,
                    'money_after'       => $member->money,
                    'operate_type'      => MemberMoneyLog::OPERATE_TYPE_MEMBER,
                    'number_type'       => MemberMoneyLog::MONEY_TYPE_ADD,
                    'user_id'           => 0,
                    'model_name'        => get_class($history),
                    'model_id'          => $historyId
                ]);
            }

            DB::commit();
        } catch (\Exception $exception) {
            logInfo($exception);
            DB::rollBack();
        }

        return $this->xml();
    }

    public function xml()
    {
        return response()->xml([
            'billno' => request()->get('billno'),
            'status' => 'OK',
        ]);
    }

    protected function randomString($length = 16): string
    {
        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }


    public function bankCode(){
        $result = [];
        $bankCode = getConfig('eeziepay.bank_code');
        foreach ($bankCode as $key => $value) {
            $item['bank_name'] = $value;
            $item['bank_code'] = $key;
            $item['bank_image'] = URL('') . getBankLogo($key, true);
            array_push($result, $item);
        }
        return $this->success(['data' => $result]);
    }

    public function bankQrCode(){
        $result = [];
        $bankCode = getConfig('eeziepay.bank_qr_code');
        foreach ($bankCode as $key => $value) {
            $item['bank_name'] = $value;
            $item['bank_code'] = $key;
            $item['bank_image'] = URL('') . getBankLogo($key, true);
            array_push($result, $item);
        }
        return $this->success(['data' => $result]);
    }
}
