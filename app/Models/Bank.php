<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $guarded = ['id'];

    public static $list_field = [
        // 'id' => ['name' => 'ID','is_show' => false],
        'key' => ['name' => 'Logo','type' => 'text','is_show' => true],
        'name' => ['name' => 'Tên','type' => 'text','is_show' => true],
        'url' => ['name' => 'URL','type' => 'text','is_show' => true],
        'logo' => ['name' => 'Logo','type' => 'picture','is_show' => true],
        'is_open' => ['name' => 'Trạng thái','type' => 'radio','validate' => 'required','data' => 'platform.is_open','is_show' => true,'style' => 'platform.style_boolean'],
        'weight' => ['name' => 'Số','type' => 'number'],
        'lang' => ['name' => 'Ngôn ngữ/Tiền tệ','type' => 'select','is_show' => true,'data' => 'platform.lang_select'],
    ];

    // App\Models\Bank::getBankArray();
    public static function getBankArray($lang = Base::LANG_CN){
        return self::where('lang',$lang)->getBankArrayCondition()->toArray();
    }

    // App\Models\Bank::getAllBankArray();
    public static function getAllBankArray(){
        return self::getBankArrayCondition();
    }

    // App\Models\Bank::getBank();
    public static function getBank($key = null)
    {
        $currentLanguage = request()->user('web')->lang ?? session('applocale', 'vi');
        $langs = [Base::LANG_COMMON, $currentLanguage];

        return self::whereIn('lang', $langs)
            ->when($key, function ($q) use ($key) {
                $q->where('key', $key);
            })
            ->where('is_open',1)
            ->orderByDesc('weight')
            ->first()
            ->toArray();
    }

    public function scopeGetBankArrayCondition($query){
        return $query->where('is_open',1)
            ->orderByDesc('weight')->pluck('name','key');
    }
}
