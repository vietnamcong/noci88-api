<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberWheel extends Base
{
    public $guarded = ['id'];

    public static $list_field = [
        'member_id' => ['name' => 'Mã thành viên', 'type' => 'number', 'is_show' => true],
        'user_id' => ['name' => 'ID quản trị viên', 'type' => 'number', 'is_show' => false],
        'award_id' => ['name' => 'ID giải thưởng','type' => 'number','is_show' => false],
        'award_desc' => ['name' => 'Mô tả giải thưởng','type' => 'number','is_show' => true],
        'status' => ['name' => 'Nhận trạng thái','type' => 'select','is_show' => true,'data' => 'platform.wheel_status']
    ];

    const STATUS_UNDEAL = 1; // Đang chờ xác nhận
    const STATUS_SUCCESS = 2; // Nhận thưởng thành công
    const STATUS_FAILED = 3; //  Nhận thưởng thất bại

    protected $appends = ['status_text'];

    public function getStatusTextAttribute(){
        $wheel_status = trans('res.option.wheel_status');
        return data_get($wheel_status,$this->attributes['status']);
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Member');
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
