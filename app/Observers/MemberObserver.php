<?php

namespace App\Observers;

use App\Models\Member;
use Exception;
use Zhuzhichao\IpLocationZh\Ip;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class MemberObserver
{
    /**
     * @param member $member
     */
    public function creating(Member $member){
        // 在创建的时候，生产用户的 api 密码
        if($member->name){
            $member->original_password = substr(md5(bcrypt($member->name)), 0,10);
        }

        // 如果创建时没有邀请码，则默认自动生成
        if(!$member->invite_code){
            $member->invite_code = Str::random(7);
        }

        try{
            if(!$member->register){
                $ipDetails = Ip::find(get_client_ip());
                if (is_string($ipDetails)) {
                    $ipDetails = explode(',', $ipDetails);
                }
                $member->register_ip = get_client_ip();
                $member->register_area = implode(' ', $ipDetails);
                $member->register_site = self::getFullUrl() ?? trim($_SERVER['SERVER_NAME']) ?? '';
            }
        }catch(Exception $e){
            $member->register_ip = '';
        }

        $this->operatePassword($member);
    }

    public function updating(Member $member){
        $this->operatePassword($member);
        //$member->clearPermissionAndMenu();
    }

    // 更新 和 创建 的时候，判断是否需要加密密码
    // 修改原始密码
    public function operatePassword(Member $member){
        if($member->password){
            // 记录用户的原始密码
            if(Hash::needsRehash($member->password)){
                $member->o_password = $member->password;
                $member->password = bcrypt($member->password);
            }
        }

    }

    protected function getFullUrl() {
        // Kiểm tra xem kết nối có được bảo vệ bằng SSL không
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        // Xác định schema
        $protocol = $https ? 'https://' : 'http://';

        // Lấy tên miền
        $domain = trim($_SERVER['SERVER_NAME']);

        // Tạo và trả về URL đầy đủ
        return $protocol . $domain;
    }
}
