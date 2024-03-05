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
    protected $mb_url = 'historymbbank';  // historyapimbbank
    protected $vt_url = 'historyviettin';  // historyapiviettin
    protected $vc_url = 'historyvietcombank';  // historyapivcb
    protected $ac_url = 'historyacb'; // historyapiacb
    protected $bidv_url = 'historybidv';  // historyapibidv

    protected $bank_mb  = 'MB';
    protected $bank_vt  = 'VT';
    protected $bank_vc  = 'VC';
    protected $bank_ac  = 'AC';
    protected $bank_bidv  = 'BIDV';

    public function cron_bank() {
        $this->cron_mb();
        $this->cron_vc();
        $this->cron_ac();
        $this->cron_bidv();
        $this->setStatusCode(200);
        return $this->success([
            'data' => 'Call Bank Cron Success V New!'
        ]);  
    }
    // CRON MB
    public function cron_mb() {
        $payment = Payment::where('is_open', 1)->whereNotNull('callback_url')
        ->where('callback_url', 'like', '%' . $this->mb_url . '%')->get();

        foreach ($payment as $key => $value) {
            if($value->callback_url != null){
               $this->history($value->callback_url, $this->bank_mb);
            }
        }
        return $this->success([
            'data' => 'Call Bank Cron MB Success !'
        ]);
    }
    // CRON VT
    public function cron_vt() {
        $payment = Payment::where('is_open', 1)->whereNotNull('callback_url')
        ->where('callback_url', 'like', '%' . $this->vt_url . '%')->get();

        foreach ($payment as $key => $value) {
            if($value->callback_url != null){
               $this->history($value->callback_url, $this->bank_vt);
            }
        }
        return $this->success([
            'data' => 'Call Bank Cron VT Success !'
        ]);
    }
    // CRON VC
    public function cron_vc() {
        $payment = Payment::where('is_open', 1)->whereNotNull('callback_url')
        ->where('callback_url', 'like', '%' . $this->vc_url . '%')->get();

        foreach ($payment as $key => $value) {
            if($value->callback_url != null){
               $this->history($value->callback_url, $this->bank_vc);
            }
        }
        return $this->success([
            'data' => 'Call Bank Cron VC Success !'
        ]);
    }
    // CRON AC
    public function cron_ac() {
        $payment = Payment::where('is_open', 1)->whereNotNull('callback_url')
        ->where('callback_url', 'like', '%' . $this->ac_url . '%')->get();

        foreach ($payment as $key => $value) {
            if($value->callback_url != null){
                $this->history($value->callback_url, $this->bank_ac);
            }
        }
        return $this->success([
            'data' => 'Call Bank Cron AC Success !'
        ]);
    }
    // CRON BIDV
    public function cron_bidv() {
        $payment = Payment::where('is_open', 1)->whereNotNull('callback_url')
        ->where('callback_url', 'like', '%' . $this->bidv_url . '%')->get();

        foreach ($payment as $key => $value) {
            if($value->callback_url != null){
            $this->history($value->callback_url, $this->bank_bidv);
            }
        }
        return $this->success([
            'data' => 'Call Bank Cron BIDV Success !'
        ]);
    }

    // COMMONT
    public function history($url, $bank = null) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $tranList = $this->checkResponse($url, json_decode($response), $bank);
        $recharges = $this->rechargeList();
        
        if($tranList != null && count($tranList) > 0){
            if($tranList != null){
                foreach ($tranList as $key => $value) {
                    foreach ($recharges as $k => $val) {
                        if($bank == $this->bank_mb){
                            if( (strpos(data_get($value, 'description'), $val->bill_no) >= 0 || strpos(data_get($value, 'description'),strtoupper($val->bill_no)) >= 0 ) && data_get($value, 'creditAmount') / 1000 == $val->money){
                                writelog('bill_no : '. $val->bill_no);
                                $data = [
                                    'account' => data_get($value, 'accountNo'),
                                    'credit_amount' => data_get($value, 'creditAmount'),
                                ];
                                $this->changeMoney($val->bill_no, $val->member_id, $data);
                            }
                        }
                        if($bank == $this->bank_vt){
                            if( (strpos(data_get($value, 'remark'), $val->bill_no) >= 0 || strpos(data_get($value, 'remark'),strtoupper($val->bill_no)) >= 0 ) && data_get($value, 'amount') / 1000 == $val->money){
                                writelog('bill_no : '. $val->bill_no);
                                $data = [
                                    'account' => data_get($value, 'corresponsiveAccount'),
                                    'credit_amount' => data_get($value, 'amount'),
                                ];
                                $this->changeMoney($val->bill_no, $val->member_id, $data);
                            }
                        }
                        if($bank == $this->bank_vc){
                            if( (strpos(data_get($value, 'Description'), $val->bill_no) >= 0 || strpos(data_get($value, 'Description'),strtoupper($val->bill_no)) >= 0 ) && str_replace(",","" ,data_get($value, 'amount')) / 1000 == $val->money){
                                writelog('bill_no : '. $val->bill_no);
                                $data = [
                                    'credit_amount' => str_replace(",","" ,data_get($value, 'amount')),
                                ];
                                $this->changeMoney($val->bill_no, $val->member_id, $data);
                            }
                        }
                        if($bank == $this->bank_ac){
                            if( (strpos(data_get($value, 'description'), $val->bill_no) >= 0 || strpos(data_get($value, 'description'),strtoupper($val->bill_no)) >= 0 ) && data_get($value, 'amount') / 1000 == $val->money){
                                writelog('bill_no : '. $val->bill_no);
                                $data = [
                                    'credit_amount' => data_get($value, 'amount'),
                                ];
                                $this->changeMoney($val->bill_no, $val->member_id, $data);
                            }
                        }
                        if($bank == $this->bank_bidv){
                            if( (strpos(data_get($value, 'txnRemark'), $val->bill_no) >= 0 || strpos(data_get($value, 'txnRemark'),strtoupper($val->bill_no)) >= 0 ) && data_get($value, 'amount') / 1000 == $val->money){
                                writelog('bill_no : '. $val->bill_no);
                                $data = [
                                    'credit_amount' => data_get($value, 'amount'),
                                ];
                                $this->changeMoney($val->bill_no, $val->member_id, $data);
                            }
                        }
                    }
                }
            }
        }else{
            var_dump($tranList);
        }
        
    }
    
    public function rechargeList() {
        $recharges = Recharge::where('status', Recharge::STATUS_UNDEAL)->get();
        return $recharges;
    }

    public function checkResponse($url, $response, $bank = null) {
        if($bank == $this->bank_mb){
            return data_get($response, 'TranList');
        }

        if($bank == $this->bank_vt){
            return data_get($response, 'transactions');
        }

        if($bank == $this->bank_vc){
            return data_get($response, 'transactions');
        }

        if($bank == $this->bank_ac){
            return data_get($response, 'data');
        }

        if($bank == $this->bank_bidv){
            return data_get($response, 'txnList');
        }
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
    public function changeMoney($bill_no, $member_id, $data){
        $member = Member::where('id', $member_id)->first();
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

    public function checkRechargeStatus($bill_no) {
        $recharge = Recharge::where('bill_no', $bill_no)->first();
        if($recharge == null){
            return $this->failed(trans('res.base.operate_fail'));
        }else{
            if($recharge->status != Recharge::STATUS_UNDEAL) {
                return $this->success([
                    'data' => $recharge,
                    'message'=>trans('res.recharge.msg.recharge_dealed')
                ]); 
            }else{
                return $this->success([
                    'data' => $recharge,
                    'message'=>trans('res.recharge.msg.recharge_falid')
                ]); 
            }
        }
    }
}