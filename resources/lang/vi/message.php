<?php

return [
    'success' => 'Thành công',
    'error' => 'Thất bại',
    'operate_error' => 'Hoạt động bất thường',
    'account_block' => 'Tài khoản đã bị khóa. Không thể xác thực',
    'image_limit_quantity' => 'Số lượng ảnh vượt quá giới hạn cho phép là :quantity',
    'image_limit_size' => 'Ảnh vượt quá dung lượng cho phép là :size',
    'image_valid' => 'Không thể nhận dạng loại ảnh',

    'log' => [
        'login_error' => 'Đăng nhập thất bại, lý do thất bại: :err. Tài khoản đăng nhập là: :name, Mật khẩu là: :password',
        'login_success' => 'Thành viên :name đã đăng nhập thành công',
    ],

    'member' => [
        'not_found' => 'Tài khoản không tồn tại',
        'status_forbidden' => 'Tài khoản đã bị khóa',
        'status_force_off' => 'Tài khoản đã bị tắt',
    ],

    'login' => [
        'username_required' => 'username là bắt buộc',
        'username_min' => 'username ít nhất là 6 ký tự',
        'username_max' => 'username nhiều nhất là 20 ký tự',
        'password_required' => 'Mật khẩu là bắt buộc',
        'password_min' => 'Mật khẩu ít nhất là 6 ký tự',
        'key_required' => 'Key là bắt buộc',
        'key_string' => 'Key là chuỗi ký tự',
        'captcha_required' => 'Capcha là bắt buộc',
        'captcha_api' => 'Key và Captcha không trùng khớp',
        'error' => 'Tài khoản hoặc mật khẩu sai',
    ],

    'register' => [
        'username_required' => 'username là bắt buộc',
        'username_min' => 'username ít nhất là 6 ký tự',
        'username_max' => 'username nhiều nhất là 20 ký tự',
        'username_unique' => 'username là duy nhất',
        'username_regex' => 'username không hợp lệ',
        'password_required' => 'Mật khẩu là bắt buộc',
        'password_min' => 'Mật khẩu ít nhất là 6 ký tự',
        'password_confirmation_required' => 'Mật khẩu xác nhận là bắt buộc',
        'password_confirmation_min' => 'Mật khẩu xác nhận ít nhất 6 ký tự',
        'password_confirmation_same' => 'Mật khẩu xác nhận không trùng khớp',
        'phone_required' => 'Số điện thoại là bắt buộc',
        'phone_numeric' => 'Số điện thoại không đúng định dạng',
        'phone_min' => 'Số điện thoại ít nhất là 10 số',
        'qk_pwd_required' => 'Mật khẩu rút tiền là bắt buộc',
        'qk_pwd_min' => 'Mật khẩu rút tiền ít nhất 6 ký tự',
        'qk_pwd_numeric' => 'Mật khẩu rút tiền phải là số',
        'realname_required' => 'Họ & tên là bắt buộc',
        'realname_min' => 'Họ & tên ít nhất 2 ký tự',
        'realname_max' => 'Họ & tên nhìu nhất 50 ký tự',
        'lang_required' => 'Ngôn ngữ là bắt buộc',
        'lang_invalid' => 'Ngôn ngữ không hợp lệ',
        'key_required' => 'Key là bắt buộc',
        'key_string' => 'Key là chuỗi ký tự',
        'captcha_required' => 'Capcha là bắt buộc',
        'captcha_api' => 'Key và Captcha không trùng khớp',
    ],

    'logout' => [
        'success' => 'Đăng xuất thành công',
    ],

    'refresh_token' => [
        'unauthorized' => 'Không được phép. Token sai',
    ],

    'change_password' => [
        'password_old_required' => 'Mật khẩu cũ là bắt buộc',
        'password_old_min' => 'Mật khẩu cũ ít nhất 6 ký tự',
        'password_required' => 'Mật khẩu là bắt buộc',
        'password_confirmed' => 'Mật khẩu phải trùng khớp nhau',
        'password_min' => 'Mật khẩu ít nhất 6 ký tự',
        'password_different' => 'Mật khẩu mới không trùng lặp với mật khẩu cũ',
        'password_confirm_required' => 'Mật khẩu xác nhận là bắt buộc',
        'password_confirm_min' => 'Mật khẩu xác nhận ít nhất 6 ký tự',
        'password_confirm_same' => 'Mật khẩu xác nhận không trùng khớp',
        'success' => 'Cập nhật mật khẩu thành công',
        'password_old_invalid' => 'Mật khẩu cũ không chính xác',
    ],

    'member_bank' => [
        'bank_not_found' => 'Tài khoản ngân hàng Không tồn tại',
    ],

    'member_create_bank' => [
        'card_no_required' => 'Số tài khoản là bắt buộc',
        'card_no_min' => 'Số tài khoản ít nhất 10 số',
        'bank_type_required' => 'Loại tài khoản là bắt buộc',
        'bank_type_invalid' => 'Loại tài khoản không hợp lệ',
        'owner_name_required' => 'Chủ tài khoản là bắt buộc',
        'bank_address_required' => 'Địa chỉ là bắt buộc',
        'phone_required' => 'Số điện thoại là bắt buộc',
        'success' => 'Thêm tài khoản ngân hàng thành công',
        'error' => 'Thêm tài khoản ngân hàng thất bại',
    ],

    'member_update_bank' => [
        'card_no_required' => 'Số tài khoản là bắt buộc',
        'card_no_min' => 'Số tài khoản ít nhất 10 số',
        'bank_type_required' => 'Loại tài khoản là bắt buộc',
        'bank_type_invalid' => 'Loại tài khoản không hợp lệ',
        'owner_name_required' => 'Chủ tài khoản là bắt buộc',
        'bank_address_required' => 'Địa chỉ là bắt buộc',
        'phone_required' => 'Số điện thoại là bắt buộc',
        'success' => 'Cập nhật tài khoản ngân hàng thành công',
        'error' => 'Cập nhật tài khoản ngân hàng thất bại',
    ],

    'member_message_read' => [
        'ids_required' => 'Danh sách ids là bắt buộc',
        'member_message_read.state_required' => 'Trạng thái là bắt buộc',
        'member_message_read.state_boolean' => 'Trạng thái không hợp lệ',
    ],

    'agent' => [
        'not_agent' => 'Tài khoản chưa phải là Đại Lý',
    ],

    'modify_pwd' => [
        'old_qk_pwd_required' => 'Mật khẩu rút tiền cũ là bắt buộc',
        'old_qk_pwd_min' => 'Mật khẩu rút tiền cũ ít nhất 6 ký tự',
        'qk_pwd_required' => 'Mật khẩu rút tiền là bắt buộc',
        'qk_pwd_min' => 'Mật khẩu rút tiền ít nhất 6 ký tự',
        'qk_pwd_different' => 'Mật khẩu rút tiền mới không trùng lặp với mật khẩu rút tiền cũ',
        'qk_pwd_confirmation_required' => 'Mật khẩu rút tiền xác nhận là bắt buộc',
        'qk_pwd_confirmation_min' => 'Mật khẩu rút tiền xác nhận ít nhất 6 ký tự',
        'qk_pwd_confirmation_same' => 'Mật khẩu rút tiền xác nhận không trùng khớp',
        'qk_pwd_error' => 'Mật khẩu rút tiền gốc là sai. Vui lòng kiểm tra lại',
        'qk_pwd_set' => 'Bạn đã đặt mật khẩu rút tiền. Không cần phải cài đặt một lần nữa',
    ],

    'recharge_normal' => [
        'payment_type_required' => 'Phương thức thanh toán là bắt buộc',
        'payment_type_invalid' => 'Phương thức thanh toán không hợp lệ',
        'payment_account_required' => 'Số tài khoản là bắt buộc',
        'payment_name_required' => 'Chủ tài khoản là bắt buộc',
        'payment_amount_required' => 'Số tiền chuyển khoản là bắt buộc',
        'payment_amount_numeric' => 'Số tiền chuyển khoản phải là số',
        'payment_amount_min' => 'Số tiền chuyển khoản là ',
        'payment_amount_integer' => 'Số tiền chuyển khoản phải là số nguyên',
        'payment_id_required' => 'Mã thanh toán là bắt buộc',
        'payment_between' => 'Số tiền chuyển khoản là :min ~ :max. Vui lòng kiểm tra lại',
        'payment_closed' => 'Phương thức thanh toán không hợp lệ. Vui lòng chọn lại',
        'payment_request' => 'Số tiền nạp tiền cuối cùng của thành viên là【 :money 】',
        'success' => 'Nạp tiền thành công. Vui lòng chờ quản trị viên xem xét',
        'error' => 'Nạp tiền thất bại. Vui lòng thử lại',
    ],

    'lang_fields' => [
        'common' => 'Tất cả',
        'zh_cn' => 'Tiếng Trung',
        'zh_hk' => 'Tiếng Hồng Kông',
        'en' => 'Tiếng Anh',
        'th' => 'Tiếng Thái',
        'vi' => 'Tiếng Việt'
    ],

    'recharge_status' => [
        1 => 'Đang chờ xác nhận',
        2 => 'Nạp tiền thành công',
        3 => 'Nạp tiền thất bại'
    ],

    'drawing_status' => [
        1 => 'Đang chờ xác nhận',
        2 => 'Rút tiền thành công',
        3 => 'Rút tiền thất bại'
    ],

    'recharge_type' => [
        1 => 'Alipay',
        2 => 'WeChat',
        3 => 'Chuyển khoản ngân hàng',
        4 => 'Thanh toán của bên thứ ba',
        5 => 'QQ',
        6 => 'WeChat nhanh',
        7 => 'Alipay nhanh'
    ],

    'activity_type' => [
        1 => 'Hoạt động ví hoàn trả',
        2 => 'Các hoạt động lợi tức',
        3 => 'Hoạt động nạp tiền',
        4 => 'Cho thấy sự kiện'
    ],

    'payment_type' => [
        'online_alipay' => 'Thanh toán qua Alipay (thanh toán trực tuyến)',
        'online_wechat' => 'WeChat Pay (Thanh toán trực tuyến)',
        'online_union_quick' => 'UnionPay Express (Thanh toán trực tuyến)',
        'online_union_scan' => 'Mã quét UnionPay (thanh toán trực tuyến)',
        'online_eezipay' => 'Thanh toán Eezipay (Thanh toán trực tuyến)',
        'company_bankpay' => 'Chuyển khoản qua thẻ ngân hàng',
        'company_alipay' => 'Thanh toán qua Alipay (tiền gửi công ty)',
        'company_wechat' => 'Thanh toán công khai WeChat (tiền gửi công ty)',
        'online_cgpay' => 'Thanh toán CGPay (thanh toán trực tuyến)',
        'company_usdt' => 'Thanh toán USDT (tiền gửi công ty)',
        'online_usdt' => 'Thanh toán USDT (thanh toán trực tuyến)',
    ],

    'activity' => [
        'activity_exists' => 'Khuyến mãi không tồn tại',
    ],

    'activity_apply_status' => [
        0 => 'Đang được xem xét',
        1 => 'Đã phê duyệt',
        2 => 'Kiểm toán thất bại',
        3 => 'Giảm giá đã được phát hành'
    ],

    'drawing' => [
        'bank_id_required' => 'Tài khoản ngân hàng là bắt buộc',
        'bank_id_exists' => 'Tài khoản ngân hàng không tồn tại',
        'money_required' => 'Số tiền rút là bắt buộc',
        'money_numeric' => 'Số tiền rút phải là số',
        'money_min' => 'Số tiền rút là ',
        'money_integer' => 'Số tiền rút phải là số nguyên',
        'qk_pwd_required' => 'Mật khẩu rút tiền là bắt buộc',
        'money_not_enough' => 'Số tiền rút ra lớn hơn số tiền hiện có. Vui lòng sửa đổi',
        'time_not_allow' => 'Thời gian hiện tại không thể rút tiền',
        'min_money' => 'Số tiền rút ra ít hơn số tiền rút ra tối thiểu :min',
        'max_money' => 'Số tiền rút ra cao hơn số tiền rút ra tối đa :max',
        'bank_not_exist' => 'Thông tin thẻ ngân hàng không tồn tại. Vui lòng kiểm tra lại',
        'qk_pwd_error' => 'Lỗi nhập mật khẩu rút tiền. Vui lòng thử lại',
        'times_not_enough' => 'Hôm nay có quá nhiều ứng dụng rút tiền. Hãy quay lại vào ngày mai',
        'counter_fee' => 'Lệ phí',
        'success' => 'Rút tiền thành công. Vui lòng chờ quản trị viên xem xét',
        'error' => 'Rút tiền thất bại. Vui lòng thử lại',
        'drawing_request' => 'Số tiền rút tiền cuối cùng của thành viên là【 :money 】',
    ],
];
