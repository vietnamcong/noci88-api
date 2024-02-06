<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\MemberMoneyLog;
use App\Models\Recharge;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SieuThiCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sieuthicode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CallBack Api SieuThiCode';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        writelog('CallBack Api SieuThiCode Start.');

        $ch = curl_init('https://api.sieuthicode.net/historyapimbbank/AtrBoMFdWmLw-RTMcJB-jnWt-jxca-cfyw');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $history = json_decode($response);
        $tranList = $history->TranList;

        foreach ($tranList as $key => $value) {
            $description = $value->description;
            $array = explode("-",$description, 2);
            if(is_array($array) && $array != null){
                if(count($array) == 2){
                    $member_name = $array[0];
                    $bill =  $array[1];
                    
                    $member = Member::where('name', $member_name)->first();
                    $recharge = Recharge::find(trim($bill));
                    if ($recharge->count() > 0) {
                        $recharge_update = [];
                        switch ($recharge->status) {
                            case  Recharge::STATUS_UNDEAL:
                                $recharge_update['status'] = Recharge::STATUS_SUCCESS;
                                # code...
                                break;
                            case  Recharge::STATUS_SUCCESS:
                                # code...
                                break;
                            case  Recharge::STATUS_FAILED:
                                # code...
                                break;
                            default:
                                # code...
                                break;
                        }
                    } else {
                        if(count($value) > 0){
                            $payment_detail = [
                                
                            ];
                            $recharge_new = Recharge::create([
                                'bill_no'           => $bill,
                                'name'              => $member->name,
                                'member_id'         => $member->id,
                                'account'           => '',
                                'origin_money'      => 0,
                                'money'             => $value->creditAmount / 1000,
                                'money_before'      => $beforeMoney,
                                'money_after'       => $member->money,
                                'payment_type'      => Payment::TYPE_BANKPAY,
                                'payment_detail'    => json_encode($payment_detail, JSON_UNESCAPED_UNICODE),
                                'status'            => Recharge::STATUS_UNDEAL,
                                'lang'              => $member->lang,
                                'hk_at'             => Carbon::now()->format('Y-m-d H:i:s')
                            ]);
                            writelog('CallBack Api SieuThiCode Create : '. json_encode($recharge_new));
                        }
                    }
                }
            }
        }
        writelog('CallBack Api SieuThiCode End.');
    }
}
