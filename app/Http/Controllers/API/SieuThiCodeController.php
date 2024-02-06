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


class SieuThiCodeController extends Controller
{
    protected $mb_url = 'historyapimbbank';
    protected $vt_url = 'historyapiviettin';
    protected $vc_url = 'historyapivcb';
    protected $ac_url = 'historyapiacb';
    protected $bidv_url = 'historyapibidv';

    public function cron_bank() {
        $payment = Payment::where('is_open', 1)->whereNotNull('callback_url')->get();
        foreach ($payment as $key => $value) {
            if($value->callback_url != null){
               $this->history($value->callback_url);
            }
        }
        return $this->success([
            'data' => 'Call Bank Cron Success V17 !'
        ]);  
    }

    public function history($url) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $tranList = $this->checkResponse($url, json_decode($response));

        foreach ($tranList as $key => $value) {
            $member_name = data_get($value, 'member_name');
            $bill_no = data_get($value, 'bill_no');
            if($member_name != null && $bill_no != null){
                writelog('member_name : '. $member_name);
                writelog('bill_no : '. $bill_no);
                $this->changeMoney($bill_no, $member_name, $value);
            }
        }
    }
    
    public function checkResponse($url, $response) {
        $data = [];
        $type = null;

        if(count(explode('/', $url)) > 3){
            $type = explode('/', $url)[3];
            if($type == $this->mb_url){
                $result = data_get($response, 'TranList');
                if($result != null){
                    foreach ($result as $key => $value) {
                        $array = explode(" ", data_get($value, 'description'));
        
                        $item['account'] = data_get($value, 'accountNo');
                        $item['credit_amount'] = data_get($value, 'creditAmount');
                        if(count($array) > 3){
                            $item['member_name'] = $array[1];
                            $item['bill_no'] = $array[2];
                        }
                        array_push($data, $item);
                    }
                }
            }
            if($type == $this->vt_url){
                $result = data_get($response, 'transactions');
                if($result != null){
                    foreach ($result as $key => $value) {
                        $array = explode(" ", data_get($value, 'remark'));
    
                        $item['account'] = data_get($value, 'corresponsiveAccount');
                        $item['account_name'] = data_get($value, 'corresponsiveName');
                        $item['credit_amount'] = data_get($value, 'amount');
                        if(count($array) > 4){
                            $item['member_name'] = $array[2];
                            $item['bill_no'] = $array[3];
                        }
                        array_push($data, $item);
                    }
                }
            }
            if($type == $this->vc_url){
                $result = data_get($response, 'transactions');
                
                if($result != null){
                    foreach ($result as $key => $value) {
                        $array = explode(" ", data_get($value, 'Remark'));
    
                        $item['credit_amount'] = data_get($value, 'Amount');
    
                        if(count($array) > 2){
                            $item['member_name'] = $array[2];
                            $item['bill_no'] = $array[3];
                        }
                        array_push($data, $item);
                    }
                }
            }
            if($type == $this->ac_url){
                $result = data_get($response, 'data');
                if($result != null){
                    foreach ($result as $key => $value) {
                        $array = explode(" ", data_get($value, 'description'));
    
                        $item['account'] = data_get($value, 'account');
                        $item['account_name'] = data_get($value, 'accountName');
                        $item['credit_amount'] = data_get($value, 'amount');
                        if(count($array) > 4){
                            $item['member_name'] = $array[2];
                            $item['bill_no'] = $array[3];
                        }
                        array_push($data, $item);
                    }
                }
            }
            if($type == $this->bidv_url){
                $result = data_get($response, 'txnList');
                if($result != null){
                    foreach ($result as $key => $value) {
                        $array = explode(" ", data_get($value, 'txnRemark'));
    
                        $item['credit_amount'] = data_get($value, 'amount');
                        if(count($array) > 5){
                            $item['member_name'] = $array[4];

                            $item['bill_no'] = $array[5];
                        }
                        array_push($data, $item);
                    }
                }
            }
        }
        return $data;
    }

    public function qrcode(Request $request, $id) {
        $member = $this->getMember();
        $payment = Payment::find($id);
        $data = $request->all();

        $payment_detail = $payment->params;

        $this->validateRequest($data,[
            'amount'        => 'required'
        ]);
        if(data_get($payment_detail, 'acqid') != null){
            $url = 'https://api.vietqr.io/v2/generate';
            $addInfo = $member->name . ' ' . getBillNo();
    
            $params = [
                "accountNo"     => $payment->account,
                "accountName"   => $payment->name,
                "acqId"         => data_get($payment_detail, 'acqid'),
                "amount"        => data_get($data, 'amount'),
                "addInfo"       => $addInfo,
                "format"        => "text",
                "template"      => "compact"
            ];
    
            $ch = curl_init( $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $response = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($response);
            if($response->code == '00'){
                $data = [
                    'qrcode'    => $response->data,
                    'params'    => $params
                ];
                return $this->success([
                    'data' => $data
                ]); 
            }else{
                return $this->failed($response);
            }
        }
    }

    // 通过充值验证
    public function changeMoney($bill_no,$member_name, $data){
        $member = Member::where('name', $member_name)->first();
        $recharge = Recharge::where('bill_no', $bill_no)->first();
        
        $data = [];
        if($recharge == null) return $this->failed(trans('res.recharge.msg.recharge_dealed'));

        if($recharge->status != Recharge::STATUS_UNDEAL) return $this->failed(trans('res.recharge.msg.recharge_dealed'));

        $data['status'] = Recharge::STATUS_SUCCESS;
        $data['confirm_at'] = Carbon::now()->toDateTimeString();
        $data['user_id'] = '';

        try{
            DB::transaction(function() use ($data,$recharge){
                $m = $recharge->money;

                $data['before_money'] = $recharge->member->money;
                $data['after_money'] = $data['before_money'] + $recharge->money;

                $data['name'] = data_get($data, 'account_name') == null ? '' : data_get($data, 'account_name');
                $data['account'] = data_get($data, 'account') == null ? '' : data_get($data, 'account');
                $data['user_id'] = 0;
                // 记录充值日志
                // update last money log or create new
                $log = MemberMoneyLog::where('member_id', $recharge->member_id)
                    ->where('operate_type', MemberMoneyLog::OPERATE_TYPE_RECHARGE_ACTIVITY)
                    ->where('model_name', get_class($recharge))
                    ->where('model_id', $recharge->id)
                    ->first();

                if (empty($log)) {
                    MemberMoneyLog::create([
                        'member_id' => $recharge->member_id,
                        'money' => $recharge->money,
                        'money_before' => $data['before_money'],
                        'money_after' => $data['before_money'] + $recharge->money,
                        'operate_type' => MemberMoneyLog::OPERATE_TYPE_MEMBER,
                        'number_type' => MemberMoneyLog::MONEY_TYPE_ADD,
                        'user_id' => $data['user_id'],
                        'model_name' => get_class($recharge),
                        'model_id' => $recharge->id
                    ]);
                } else {
                    $log->money = $recharge->money;
                    $log->money_before = $data['before_money'];
                    $log->money_after = $data['before_money'] + $recharge->money;
                    $log->operate_type = MemberMoneyLog::OPERATE_TYPE_MEMBER;
                    $log->number_type = MemberMoneyLog::MONEY_TYPE_ADD;
                    $log->user_id = $data['user_id'];
                    $log->save();
                }

                $recharge->update($data);
                writelog('Chame Money : '. $m);
                $recharge->member->increment('money',$m);
                $recharge->addRechargeML();
                writelog('End Chame Money ');
            });
        }catch(Exception $e){
            return $this->failed(trans('res.base.update_fail').$e->getMessage());
        }
        return $this->success(['close_reload' => true], trans('res.base.update_success'));
    }
}