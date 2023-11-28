<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YuebaoPlan extends Base
{
    protected $guarded = ['id'];

    public static $list_field = [
        'SettingName' => ['name' => 'T�n d? �n','type' => 'text','validate' => 'required','is_show' => true],
        'MinAmount' => ['name' => 'S? ti?n mua t?i thi?u','type' => 'number','validate' => 'required','is_show' => false],
        'MaxAmount' => ['name' => 'S? ti?n mua t?i da','type' => 'number','validate' => 'required','is_show' => false],
        'SettleTime' => ['name' => 'Th?i gian gi?i quy?t (gi?)','type' => 'number','validate' => 'required','is_show' => false],
        'IsCycleSettle' => ['name' => 'Phuong ph�p gi?i quy?t','type' => 'radio','validate' => 'required','data' => 'platform.yuebao_settle_type','is_show' => true],
        'Rate' => ['name' => 'T? l? chuong tr�nh','type' => 'number','validate' => 'required','is_show' => true],
        //'RemainingCount' => ['name' => '??????','type' => 'number','validate' => 'required','is_show' => true],
        'TotalCount' => ['name' => 'T?ng s? ti?n k? ho?ch','type' => 'number','validate' => 'required','is_show' => true],
        'LimitInterest' => ['name' => 'L�i su?t gi?i h?n th�nh vi�n','type' => 'number','validate' => 'required','is_show' => true],
        'LimitOrderIntervalTime' => ['name' => 'Kho?ng th?i gian d?t h�ng (gi?)','type' => 'number','is_show' => false],
        'InterestAuditMultiple' => ['name' => 'M� s? th�ch nhi?u','type' => 'number','is_show' => false],
        'LimitUserOrderCount' => ['name' => 'T?ng s? ti?n mua t?i da c?a th�nh vi�n','type' => 'number','is_show' => false],
        'lang' => ['name' => 'Ti?n t?','type' => 'select','is_show' => true,'data' => 'platform.lang_fields'],
        'is_open' => ['name' => 'N� c� m? c?a d? mua kh�ng','type' => 'radio','data' => 'platform.is_open','is_show' => true,'style' => 'platform.style_isopen'],
        'weight' => ['name' => 'Lo?i','type' => 'number','is_show' => false]
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function member_plans(){
        return $this->hasMany('App\Models\MemberYuebaoPlan','plan_id','id');
    }

    public function last_member_plans($member_id){
        return $this->hasOne('App\Models\MemberYuebaoPlan','plan_id','id')
            ->where('member_id',$member_id)
            ->orderByDesc('created_at');
    }
}
