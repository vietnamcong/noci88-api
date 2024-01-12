<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Publisher extends Base
{
    public $table = 'publishers';

    public $guarded = ['id'];

    public $hidden = ['lang_json'];

    public static $list_field = [
        'title' => ['name' => 'Tựa đề trò chơi', 'type' => 'text', 'validate' => 'required', 'is_show' => true],
        'web_pic' => ['name' => 'Hình máy tính', 'type' => 'picture', 'is_show' => true],
        'mobile_pic' => ['name' => 'Hình ảnh di động', 'type' => 'picture', 'is_show' => true],
        'is_open' => ['name' => 'Nó mở rồi', 'type' => 'radio', 'validate' => 'required', 'data' => 'platform.is_open', 'is_show' => true, 'style' => 'platform.style_boolean'],
        'lang' => ['name' => 'Ngôn ngữ','type' => 'select','is_show' => true,'data' => 'platform.lang_fields'],
    ];
    
    public function scopeGetPublisherNameArray($query){
        return $query->pluck('title','id')->toArray();
    }

    public function getLangJsonArrAttribute(){
        return json_decode($this->lang_json,1);
    }

    public function getLangTitle($lang = ''){
        if(!strlen($lang)) $lang = getRequestLang();
        return Arr::get($this->lang_json_arr,$lang,$this->title);
    }
}
