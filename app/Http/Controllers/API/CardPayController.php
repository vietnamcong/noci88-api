<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\Member;
use App\Models\MemberMoneyLog;
use App\Models\Recharge;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\SystemConfig;

class CardPayController extends Controller
{
    public function confirm(Request $request) {
        $config = SystemConfig::getConfigGroup('card_pay',Base::LANG_COMMON);
        
        $params = $request->only(['serial','pin','card_type','card_amount']);

        $this->validateRequest($params,[
            'serial' => 'required',
            'pin' => 'required',
            'card_type' => 'required',
            'card_amount' => 'required'
        ]);

        $apiKey  =  $config['api_key'];

        $content = md5(time() . rand(0, 999999).microtime(true)); // có thể điền thông tin username và các thông tin khác sau đó sử dụng dấu "." để ngăn cách dữ liệu với nhau
        $seri = $params['serial']; // string
        $pin = $params['pin']; // string
        $loaithe = $params['card_type']; // string
        $menhgia = $params['card_amount']; // string

        $url = "https://thesieutoc.net/chargingws/v2?APIkey=".$apiKey."&mathe=".$pin."&seri=".$seri."&type=".$loaithe."&menhgia=".$menhgia."&content=".$content;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_close($ch);

		$response = json_decode(curl_exec($ch));
        $http_code = 0;
		if (isset($response->status)){$http_code = 200;}
		curl_close($ch);

	    if ($http_code == 200){                          
			if ($response->status == '00' || $response->status == 'thanhcong'){
                $member = getGuard()->user();
                $beforeMoney = $member->money;
                $payment_detail = [
                    'mathe' => $pin,
                    'seri' => $seri,
                    'loaithe' => $loaithe,
                    'menhgia' => $menhgia
                ];

                DB::beginTransaction();
                try {

                    $recharge = Recharge::create([
                        'bill_no'           => $content,
                        'name'              => $member->name,
                        'member_id'         => $member->id,
                        'account'           => '',
                        'origin_money'      => 0,
                        'money'             => $menhgia / 1000,
                        'money_before'      => $beforeMoney,
                        'money_after'       => $member->money,
                        'payment_type'      => Payment::PAYMENT_CARDPAY,
                        'payment_detail'    => json_encode($payment_detail),
                        'status'            => Recharge::STATUS_UNDEAL,
                        'lang'              => $member->lang,
                        'hk_at'             => Carbon::now()->format('Y-m-d H:i:s')
                    ]);

                    DB::commit();
                } catch (\Exception $exception) {
                    logInfo($exception);
                    DB::rollBack();
                }
				//Gửi thẻ thành công, đợi duyệt.
                return $this->success([
                    'data' => $response
                ]); 
            } else if ($response->status != '00' && $response->status != 'thanhcong'){
				// thất bại ở đây
                return $this->failed([
                    'data' => $response
                ]); 
		    }
		} else {
            return $this->failed([
                'data' => 'Có lỗi máy chủ vui lòng thử lại sau!'
            ]); 
		}

    }

    public function card_info() {
        $url = 'https://thesieutoc.net/card_info.php';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
     
        return $this->success([
            'data' => json_decode($response)
        ]); 
    }

    public function callback(Request $request) {
        $params = $request->only(['status','serial','pin','card_type','amount','content','real_amount']);
        
        $recharge = Recharge::where('bill_no', $params['content'])->first();

        $member = app(Member::class)->where('id', $recharge->member_id)->first();

        $beforeMoney = $member->money;
        $member->money = $member->money + moneyConvert($params['real_amount']);
        $member->save();

        if ($recharge->count() > 0){
            if($status == 'thanhcong') {
                $payment_detail = json_decode($recharge->payment_detail);
                $payment_detail['real_amount'] = $params['real_amount'];

                //Xử lý nạp thẻ thành công tại đây.
                Recharge::where('bill_no', $params['content'])->update([
                    'status' => Recharge::STATUS_SUCCESS,
                    'payment_detail' => json_encode($payment_detail)
                ]);

                MemberMoneyLog::create([
                    'member_id'         => $recharge->member_id,
                    'money'             => $recharge->money,
                    'money_before'      => $recharge->before_money,
                    'money_after'       => $recharge->after_money,
                    'operate_type'      => MemberMoneyLog::OPERATE_TYPE_MEMBER,
                    'number_type'       => MemberMoneyLog::MONEY_TYPE_ADD,
                    'user_id'           => 0,
                    'model_name'        => get_class($recharge),
                    'model_id'          => $recharge->id
                ]);

            } else if($status == 'saimenhgia') {
                Recharge::where('bill_no', $params['content'])->update([
                    'status' => Recharge::STATUS_FAILED,
                ]);
                //Xử lý nạp thẻ sai mệnh giá tại đây.
            } else {
                //Xử lý nạp thẻ thất bại tại đây.
                Recharge::where('bill_no', $params['content'])->update([
                    'status' => Recharge::STATUS_FAILED,
                ]);
            }
        }
    }
}