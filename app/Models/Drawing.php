<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drawing extends Model
{
    protected $guarded = ['id'];

    public static $list_field = [
        'bill_no' => ['name' => 'Mã giao dịch', 'type' => 'text', 'is_show' => true],
        'member_id' => ['name' => 'Mã thành viên', 'type' => 'number', 'is_show' => true],

        'name' => ['name' => 'Tên người nhận', 'type' => 'text'],
        'money' => ['name' => 'Số tiền rút', 'type' => 'money', 'is_show' => true],
        'account' => ['name' => 'Thông tin tài khoản', 'type' => 'text'],
        'before_money' => ['name' => 'Số tiền trước khi rút tiền', 'type' => 'money', 'is_show' => false],
        'after_money' => ['name' => 'Số tiền sau khi rút tiền', 'type' => 'money', 'is_show' => false],
        'score' => ['name' => 'Điểm', 'type' => 'money', 'is_show' => false],
        'counter_fee' => ['name' => 'Phí xử lý', 'type' => 'money', 'validate' => 'required', 'is_show' => true],
        'fail_reason' => ['name' => 'Lý do thất bại', 'type' => 'text'],
        'member_bank_info' => ['name' => 'Dữ liệu ngân hàng của người dùng json', 'type' => 'text'],
        'member_remark' => ['name' => 'Ghi chú rút tiền của người dùng', 'type' => 'text'],

        'confirm_at' => ['name' => 'Xác nhận thời gian chuyển', 'type' => 'datetime'],
        'status' => ['name' => 'Tình trạng rút tiền', 'type' => 'select', 'is_show' => true, 'data' => 'platform.drawing_status', 'label_style' => [1 => 'label-warning', 2 => 'label-success', 3 => 'label-danger']],
        'user_id' => ['name' => 'ID quản trị viên', 'type' => 'number', 'is_show' => true],
    ];

    const STATUS_UNDEAL = 1; // 待确认 - Đang chờ xác nhận
    const STATUS_SUCCESS = 2; // 审核通过 - Rút tiền thành công
    const STATUS_FAILED = 3; // 审核失败 - Rút tiền thất bại

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Member', 'member_id', 'id');
    }

    public function scopeMemberName($query, $name)
    {
        return $name ? $query->whereHas('member', function ($q) use ($name) {
            $q->where('name', 'like', '%' . $name . '%');
        }) : $query;
    }

    public function scopeUserName($query, $name)
    {
        return $name ? $query->whereHas('user', function ($q) use ($name) {
            $q->where('name', 'like', '%' . $name . '%');
        }) : $query;
    }

    public function scopeMemberLang($query, $lang)
    {
        return $lang ? $query->whereHas('member', function ($q) use ($lang) {
            $q->where('lang', $lang);
        }) : $query;
    }

    protected $appends = ['status_text'];

    public function getStatusTextAttribute()
    {
        if (isset($this->attributes['status'])) {
            return isset_and_not_empty(__('message.drawing_status'), $this->attributes['status'], $this->attributes['status']);
        }
        return null;
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
