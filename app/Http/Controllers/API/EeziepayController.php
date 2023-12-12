<?php

namespace App\Http\Controllers\Api;

use App\Models\EeziepayHistory;
use App\Models\SystemConfig;
use App\Repositories\SystemConfigRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class EeziepayController 
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

    public function confirm()
    {
        // get params from session
        $requestParams = session()->get('eeziepay_deposit');
        if (empty($requestParams)) {
            return redirect()->to(route('frontend.deposit.qr'));
        }

        // get params config
        $systemConfigs = $this->systemConfigRepository
            ->where('config_group', SystemConfig::REMOTE_API)
            ->where('lang', SystemConfig::LANG_COMMON)
            ->get()
            ->pluck('value', 'name')
            ->toArray();

        $paymentAmount = data_get($requestParams, 'payment_amount');
        $paymentAmount = Str::replace(',', '', $paymentAmount);
        $params = [
            'service_version' => getConfig('eeziepay.service_version'),
            'partner_code' => data_get($systemConfigs, 'eeziepay_code'),
            'partner_orderid' => $this->makeTransactionId(),
            'member_id' => getGuard()->user()->name,
            'member_ip' => request()->ip(),
            'currency' => 'VND',
            'amount' => eeziepayMoneyConvert($paymentAmount),
            'backend_url' => route('api.eeziepay.callback'),
            'redirect_url' => route('frontend.eeziepay.redirect'),
            'bank_code' => data_get($requestParams, 'bank_code'),
            'trans_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'key' => data_get($systemConfigs, 'eeziepay_key'),
        ];

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

        $this->setViewData([
            'params' => $params
        ]);

        // return parent::confirm();
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
        return redirect()->to(route('frontend.eeziepay.deposit.success'));
    }

    public function success()
    {
        return $this->render();
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
}
