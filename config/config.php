<?php

return [
    // -- base config --
    // route alias
    'route_alias' => [
        'api' => env('API_ALIAS', 'api/v1'),
        'frontend' => env('FRONTEND_ALIAS', '/'),
        'backend' => env('BACKEND_ALIAS', 'management'),
    ],

    // area
    'area' => [
        'command' => 'batch',
        'frontend' => 'frontend',
        'api' => 'api',
        'backend' => 'backend',
    ],

    // model field
    'model_field' => [
        'created' => ['at' => 'created_at', 'by' => ''],
        'updated' => ['at' => 'updated_at', 'by' => ''],
        'deleted' => ['flag' => '', 'at' => '', 'by' => ''],
    ],

    // model field name
    'model_field_name' => [
        'deleted_flag' => 'deleted_flag',
        'created_at' => 'created_at',
        'created_by' => 'created_by',
        'updated_at' => 'updated_at',
        'updated_by' => 'updated_by',
        'deleted_at' => 'deleted_at',
        'deleted_by' => 'deleted_by',
    ],

    // deleted flag
    'deleted_flag' => [
        'off' => 0,
        'on' => 1,
    ],

    // status
    'status' => [
        'off' => 0,
        'on' => 1,
    ],

    // static version for js, css...
    'static_version' => env('STATIC_VERSION', date('YmdHis')),

    // upload
    'media_dir' => 'uploaded/media',
    'ext_blacklist' => ['php', 'phtml', 'html'],
    'tmp_upload_dir' => 'tmp_uploads',
    'no_avatar' => 'assets/css/backend/img/image_default.png',

    // file info
    'file' => [
        'default' => [
            'image' => [
                'ext' => ['jpeg', 'jpg', 'png', 'gif', 'JPG', 'JPEG', 'PNG', 'GIF'], // extension
                'size' => ['min' => 0.001, 'max' => 2], // MB
                'accept' => '.jpeg, .jpg, .png, .gif, .JPG, .JPEG, .PNG, .GIF'
            ]
        ],
    ],

    // export csv
    'csv' => [
        'users' => [
            'filename' => 'users_' . date('YmdHis'),
            'header' => ['ID', 'email', 'created_at', 'updated_at'],
        ],
    ],

    // paginate number
    'page_number' => 10,

    // gmo
    'gmo' => [
        'url' => env('GMO_URL', ''),
        'url_link_type' => env('GMO_URL_LINK_TYPE', ''),
        'public_key' => env('GMO_PUBLIC_KEY', ''),
        'hash_key' => env('GMO_HASH_KEY', ''),
        'site_id' => env('GMO_SITE_ID', ''),
        'site_pass' => env('GMO_SITE_PASS', ''),
        'shop_id' => env('GMO_SHOP_ID', ''),
        'shop_pass' => env('GMO_SHOP_PASS', ''),
    ],

    // logs
    'logs' => [
        'zip_log' => [
            'keep_day' => env('ZIP_LOG_KEEP_DAY', 5),
        ],
        'dump_db' => [
            'file_name' => 'database_backup_' . date('YmdHis') . '.sql.gz',
            'path' => database_path('/backup'),
            'max_file' => env('DUMP_DB_MAX_FILE', 7),
        ],
    ],

    // language prefix
    'language_prefix' => '_language',

    // system language
    'languages' => [
        'zh_cn' => '中国人',
        'zh_hk' => '香港語言',
        'en' => 'English',
        'th' => 'ไทย',
        'vi' => 'Tiếng Việt',
    ],

    // system currency
    'currency' => [
        'zh_cn' => 'CNY',
        'zh_hk' => 'HKD',
        'en' => 'USD',
        'vi' => 'VND',
        'th' => 'HB',
    ],

    // SBO agent
    'sbo_agent' => env('AGENT_ACCOUNT', 'noci88'),

    // Noci88 api
    'noci88_api' => [
        'login_session_key' => 'api_login_token',
        'agent_url' => env('AGENT_URL') . '/agent',
        'language_url' => env('GAME_API_URL') . '/api/language',
        'post_login_url' => env('GAME_API_URL') . '/api/auth/login',
        'post_logout_url' => env('GAME_API_URL') . '/api/auth/logout',
        'get_list_api_money_url' => env('GAME_API_URL') . '/api/game/api_moneys',
        'get_api_money_url' => env('GAME_API_URL') . '/api/game/api_money',
        'get_messages_url' => env('GAME_API_URL') . '/api/member/message/list',
        'read_message_url' => env('GAME_API_URL') . '/api/member/message/read',
        'get_notices_url' => env('GAME_API_URL') . '/api/system/notices',
        'change_password_url' => env('GAME_API_URL') . '/api/member/password/modify',
        'change_drawing_password_url' => env('GAME_API_URL') . '/api/member/drawing_pwd/modify',
        'member_register_url' => env('GAME_API_URL') . '/api/auth/register',
        'get_banners_url' => env('GAME_API_URL') . '/api/banners',
        'get_list_game_categories_url' => env('GAME_API_URL') . '/api/games/categories',
        'get_list_game_apis_url' => env('GAME_API_URL') . '/api/games/apis',
        'get_list_game_type_url' => env('GAME_API_URL') . '/api/games/list',
        'deposit_game_url' => env('GAME_API_URL') . '/api/game/deposit',
        'withdraw_game_url' => env('GAME_API_URL') . '/api/game/withdrawal',
        'login_game_url' => env('GAME_API_URL') . '/api/game/login',
        'bet_histories_url' => env('GAME_API_URL') . '/api/game/histories',
        'credit_borrow_url' => env('GAME_API_URL') . '/api/credit/borrow',
        'credit_check_url' => env('GAME_API_URL') . '/api/credit/check',
        'credit_check_history' => env('GAME_API_URL') . '/api/credit/search',
        'credit_lend_url' => env('GAME_API_URL') . '/api/credit/lend',
        'get_agent_info_url' => env('GAME_API_URL') . '/api/member/agent',
        'team_childlist_url' => env('GAME_API_URL') . '/api/team/childlist',
        'get_team_performanceDetail_url' => env('GAME_API_URL') . '/api/team/performanceDetail',
        'suggestion_send_url' => env('GAME_API_URL') . '/api/member/message/send',
        'get_list_my_feedback_url' => env('GAME_API_URL') . '/api/member/message/send_list',
        'get_payment_company_list_url' => env('GAME_API_URL') . '/api/payment/normal/list',
        'get_member_bank_url' => env('GAME_API_URL') . '/api/member/bank',
        'get_normal_deposit_url' => env('GAME_API_URL') . '/api/recharge/normal',
        'post_withdraw_url' => env('GAME_API_URL') . '/api/drawing',
        // refund
        'get_fsnow_url' => env('GAME_API_URL') . '/api/fsnow/list',
        'post_fsnow_url' => env('GAME_API_URL') . '/api/fsnow/fetch',
        'get_fs_sbo_url' => env('GAME_API_URL') . '/api/fssbo/list',
        'post_fs_sbo_url' => env('GAME_API_URL') . '/api/fssbo/fetch',
        'get_fs_sbo_saba_url' => env('GAME_API_URL') . '/api/fssbo/saba/list',
        'post_fs_sbo_saba_url' => env('GAME_API_URL') . '/api/fssbo/saba/fetch',
        'get_fs_sbo_afb_url' => env('GAME_API_URL') . '/api/fssbo/afb/list',
        'post_fs_sbo_afb_url' => env('GAME_API_URL') . '/api/fssbo/afb/fetch',
        'get_fs_sbo_bti_url' => env('GAME_API_URL') . '/api/fssbo/bti/list',
        'post_fs_sbo_bti_url' => env('GAME_API_URL') . '/api/fssbo/bti/fetch',
        // transaction histories
        'get_transactions_url' => env('GAME_API_URL') . '/api/transactions/list',
    ],

    // SBO system
    'sbo_api' => [
        'register_player' => env('SBO_API_URL') . '/web-root/restricted/player/register-player.aspx',
        'login' => env('SBO_API_URL') . '/web-root/restricted/player/login.aspx',
        'deposit' => env('SBO_API_URL') . '/web-root/restricted/player/deposit.aspx',
        'withdraw' => env('SBO_API_URL') . '/web-root/restricted/player/withdraw.aspx',
        'update_bet_setting' => env('SBO_API_URL') . '/web-root/restricted/player/update-player-bet-settings.aspx',
    ],
    'sbo_language' => [
        'vi' => 'vi-vn',
        'en' => 'en',
        'th' => 'th-th',
        'zh-cn' => 'zh-cn',
        'zh-hk' => 'zh-tw',
    ],

    // game type
    'game_type_code' => [
        1 => 'LIVE',
        2 => 'MPG',
        3 => 'SLOT',
        4 => 'LOTTERY',
        5 => 'SPORTS',
        6 => 'CHESS',
        7 => 'OTHERS',
        8 => 'POKER',
        9 => 'CASUAL',
        10 => 'TABLE',
        11 => 'LK',
        12 => 'CHICKEN',
        99 => 'KENO'
    ],

    // SBO product type
    'product_type' => [
        1 => 'Sports Book',
        3 => 'SBO Games',
        5 => 'Virtual Sports',
        7 => 'SBO Live Casino',
        9 => 'Seamless Game Provider',
        10 => 'Live Coin',
    ],

    // SBO product type
    'game_provider' => [
        44 => 'Saba Sport',
        1015 => 'Afb Sport',
        1022 => 'Bti Sport',
    ],

    // options config
    'options' => [
        'created_at' => [
            1 => 'Hôm nay',
            2 => '7 ngày qua',
            3 => '30 ngày qua',
        ],
        'game_type' => [
            1 => 'Live casino',
            2 => 'Bắn cá',
            3 => 'Slot Game',
            4 => 'Xổ số',
            5 => 'Thể thao',
            6 => 'Game bài',
            7 => 'Game khác',
            8 => 'Video Poker',
            9 => 'Casual',
            10 => 'Table Game',
            11 => 'LK',
            12 => 'Đá gà',
            99 => 'Keno'
        ],
        'game_type_code' => [
            'CB' => 'CARD & BOARDGAME',
            'ES' => 'E-GAMES',
            'SB' => 'SPORTBOOK',
            'LC' => 'LIVE-CASINO',
            'SL' => 'SLOTS',
            'LK' => 'LOTTO',
            'FH' => 'FISH HUNTER',
            'PK' => 'POKER',
            'MG' => 'MINI GAME',
            'OT' => 'OTHERS',
        ],
        'money_type' => [
            'money' => 'Ví chính',
            'fs_money' => 'Ví hoàn trả',
        ],
        'operate_type' => [
            1 => 'Hoạt động quản trị',
            2 => 'Hệ thống miễn phí',
            3 => 'Trò chơi bật/tắt',
            4 => 'Hoàn trả phát hành',
            5 => 'Đăng nhập các hoạt động để nhận được',
            6 => 'Hoạt động nạp tiền',
            7 => 'Lợi tức nền tảng',
            8 => 'Lấy một phong bì màu đỏ',
            9 => 'Nạp tiền/rút tiền',
            10 => 'Quà tặng nạp tiền',
            11 => 'Từ chối rút tiền',
            12 => 'Thất bại trong trò chơi hoàn lại',
            13 => 'Hoạt động phát hành',
            14 => 'Ủy nhiệm đại lý',
            15 => 'Xổ số bàn xoay',
            16 => 'Mua sản phẩm tài chính',
            17 => 'Sản phẩm tài chính cổ tức',
            18 => 'Hình thức hoàn lại/khấu trừ',
            19 => 'Mua lại sản phẩm tài chính',
            20 => 'Kim phần thưởng',
            21 => 'Ben châu',
            22 => 'Bằng cách này',
            23 => 'Vay mượn',
            24 => 'Vay nợ',
            25 => 'Quà tặng hàng ngày',
            26 => 'Quà tặng hàng tuần',
            27 => 'Quà tặng hàng tháng',
            28 => 'Hằng năm, tiền quà',
            29 => 'Quà khuyến mãi',
            30 => 'Hoạt động rút tiền',
        ],
    ],

    // currency
    'currency_label' => [
        'vi' => 'Việt nam đồng - VND',
        'zh_cn' => 'Nhân dân tệ - CNY',
        'zh_hk' => 'Đô-la Hồng Kông - HKD',
        'en' => 'Đô-la Mỹ - USD',
        'th' => 'Bath - THB',
    ],

    // Eeziepay
    'eeziepay' => [
        'service_version' => '3.1',
        'remarks_prefix' => 'remarks',
        'currency' => [
            'vi' => 'VND',
            'th' => 'THB',
            'en' => 'USD',
        ],
        'bank_code' => [
            'TCB.VN' => 'Ngân hàng TMCP Kỹ Thương Việt Nam - Techcombank',
            'SCM.VN' => 'Ngân hàng TMCP Sài Gòn Thương Tín - Sacombank',
            'VCB.VN' => 'Ngân hàng TMCP Ngoại thương Việt Nam - Vietcombank',
            'ACB.VN' => 'Ngân hàng TMCP Á Châu - ACB',
            'DAB.VN' => 'Ngân hàng TMCP Đông Á - DongA Bank',
            'VTB.VN' => 'Ngân hàng TMCP Công Thương Việt Nam - VietinBank',
            'BIDV.VN' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam - BIDV',
            'EXIM.VN' => 'Ngân hàng TMCP Xuất Nhập khẩu Việt Nam - Eximbank',
            'VBARD.VN' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam - Agribank',
        ],
        'bank_qr_code' => [
            'ACB.QR.VN' => 'Ngân hàng TMCP Á Châu - ACB',
            'BIDV.QR.VN' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam - BIDV',
            'VCB.QR.VN' => 'Ngân hàng TMCP Ngoại thương Việt Nam - Vietcombank',
            'VTB.QR.VN' => 'Ngân hàng TMCP Công Thương Việt Nam - VietinBank',
            'VPB.QR.VN' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng - VPBank',
            'MB.QR.VN' => 'Ngân hàng Quân đội - MBBank',
            'TCB.QR.VN' => 'Ngân hàng TMCP Kỹ Thương Việt Nam - Techcombank',
        ],
    ],

    // banking logo
    'bank_logo' => [
        'eeziepay' => [
            'acb' => '/assets/img/bank/acb.png',
            'bidv' => '/assets/img/bank/bidv.png',
            'vcb' => '/assets/img/bank/vietcombank.png',
            'vtb' => '/assets/img/bank/vietinbank.png',
            'vpb' => '/assets/img/bank/vpbank.png',
            'mb' => '/assets/img/bank/mbbank.png',
            'dab' => '/assets/img/bank/dongabank.png',
            'exim' => '/assets/img/bank/eximbank.png',
            'vbard' => '/assets/img/bank/agribank.png',
            'scm' => '/assets/img/bank/sacombank.png',
            'sea' => '/assets/img/bank/seabank.png',
            'tcb' => '/assets/img/bank/techcombank.png',
        ],
        'banking' => [
            'vietcombank' => '/assets/img/bank/vietcombank.png',
            'sacombank' => '/assets/img/bank/sacombank.png',
            'techcombank' => '/assets/img/bank/techcombank.png',
            'mbbank' => '/assets/img/bank/mbbank.png',
            'bidv' => '/assets/img/bank/bidv.png',
            'vietinbank' => '/assets/img/bank/vietinbank.png',
            'vpbank' => '/assets/img/bank/vpbank.png',
        ],
    ],
];
