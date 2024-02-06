<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class MemberWheelAward extends Base
{
    public $table = 'member_wheel_awards';

    public $guarded = ['id'];
    
    public $hidden = ['lang_json'];

    public static $list_field = [
        'title' => ['name' => 'Tiêu đề', 'type' => 'text', 'validate' => 'required', 'is_show' => true],
        'web_pic' => ['name' => 'Hình máy tính', 'type' => 'picture', 'is_show' => true],
        'money'   => ['name' => 'Số tiền', 'type' => 'text', 'is_show' => true], 
        'type'   => ['name' => 'Loại', 'type' => 'select', 'is_show' => true, 'data' => 'platform.member_wheel_award'], 
        'mobile_pic' => ['name' => 'Hình ảnh di động', 'type' => 'picture', 'is_show' => true],
        'is_open' => ['name' => 'Nó mở rồi', 'type' => 'radio', 'validate' => 'required', 'data' => 'platform.is_open', 'is_show' => true, 'style' => 'platform.style_boolean'],
        'lang' => ['name' => 'Ngôn ngữ','type' => 'select','is_show' => true,'data' => 'platform.lang_fields'],
    ];

    const STATUS_OTHOR = 1; // Khác
    const STATUS_MONEY = 2; // Money

    public function getLangJsonArrAttribute(){
        return json_decode($this->lang_json,1);
    }

    public function getLangTitle($lang = ''){
        if(!strlen($lang)) $lang = getRequestLang();
        return Arr::get($this->lang_json_arr,$lang,$this->title);
    }
}
