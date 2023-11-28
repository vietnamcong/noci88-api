<?php

namespace App\Models;

class EeziepayHistory extends Base
{
    const STATUS_SUCCESS = '000';
    const STATUS_PENDING = '001';
    const STATUS_BANK_SUCCESS = '002';
    const STATUS_EXPIRED = '110';
    const STATUS_FAIL = '111';
    const STATUS_LOGIN_ERROR = '112';
    const STATUS_AMOUNT_ERROR = '113';
    const STATUS_PIN_ERROR = '114';
    const STATUS_PIN_TIMEOUT = '115';
    const STATUS_LOGIN_TIMEOUT = '116';
    const STATUS_ACCOUNT_TIMEOUT = '117';
    const STATUS_SECURITY_QUESTION_ERROR = '118';
    const STATUS_USER_ABORT = '119';
    const STATUS_WAITING = '999';

    public $guarded = ['id'];

    public static $list_field = [
        'billNo' => ['name' => 'Mã bill', 'type' => 'text', 'is_show' => true],
        'api_name' => ['name' => 'ID Api', 'type' => 'text', 'is_show' => true],
        'name' => ['name' => 'Tài khoản người chơi', 'type' => 'text', 'is_show' => true],
        'gameType' => ['name' => 'Loại trò chơi', 'is_show' => true, 'type' => 'select', 'data' => 'platform.game_type'],
        'status' => ['name' => 'Tình trạng thanh toán', 'is_show' => true, 'type' => 'select', 'data' => 'platform.gamerecord_status'],
        'betTime' => ['name' => 'Thời gian cá cược', 'type' => 'text', 'is_show' => true],
        'betAmount' => ['name' => 'Số tiền đặt cược', 'type' => 'text', 'is_show' => true],
        'validBetAmount' => ['name' => 'Số tiền đặt cược hiệu quả', 'type' => 'text', 'is_show' => true],
        'netAmount' => ['name' => 'Số tiền thanh toán', 'type' => 'text', 'is_show' => false],
        'roundNo' => ['name' => 'Hiển thị thông tin', 'type' => 'text', 'is_show' => false],
        'playDetail' => ['name' => 'Chi tiết trò chơi', 'type' => 'text', 'is_show' => false],
        'wagerDetail' => ['name' => 'Chi tiết bên dưới', 'type' => 'text', 'is_show' => false],
        'gameResult' => ['name' => 'Kết quả xổ số', 'type' => 'text', 'is_show' => false],
    ];

    public static function statusConfig()
    {
        return [
            self::STATUS_SUCCESS => 'Đã chuyển tiền',
            self::STATUS_PENDING => 'Tạm hoãn',
            self::STATUS_BANK_SUCCESS => 'Thành công',
            self::STATUS_EXPIRED => 'Hết hạn',
            self::STATUS_FAIL => 'Thất bại',
            self::STATUS_LOGIN_ERROR => 'Đăng nhập lỗi',
            self::STATUS_AMOUNT_ERROR => 'Số tiền không chính xác',
            self::STATUS_PIN_ERROR => 'Mã PIN không chính xác',
            self::STATUS_PIN_TIMEOUT => 'Hết hạn PIN',
            self::STATUS_LOGIN_TIMEOUT => 'Hết hạn đăng nhập',
            self::STATUS_ACCOUNT_TIMEOUT => 'Hết hạn tài khoản',
            self::STATUS_SECURITY_QUESTION_ERROR => 'Câu hỏi bảo mật không đúng',
            self::STATUS_USER_ABORT => 'Người dùng đã hủy',
            self::STATUS_WAITING => 'Đang chờ chuyển tiền',
        ];
    }

    public static function getStatus($status)
    {
        return data_get(self::statusConfig(), $status);
    }

    public function getStatusText()
    {
        return data_get(self::statusConfig(), $this->status);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
