<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberLog;
use Tymon\JWTAuth\Facades\JWTAuth;
use Zhuzhichao\IpLocationZh\Ip;


class MemberLogService
{
    private $ip = null;

    public $member;

    public function __construct()
    {
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getLogFormatter($type, $description = '', $remark = '')
    {
        $ip = $this->getIp();

        if (empty($ip)) {
            $ip = get_client_ip() ?? '';
        }

        $ipaddress = Ip::find($ip);

        return [
            'member_id' => $this->member->id ?? 0,
            'ip' => $ip,
            'address' => is_array($ipaddress) ? implode(' ', $ipaddress) : $ip,
            'ua' => request()->userAgent(),
            'type' => $type,
            'description' => $description,
            'remark' => $remark
        ];
    }

    public function memberLoginLogCreate($err = '', $token = '', $remark = '')
    {
        $this->member = request()->user('member');
        // $description = $err
        //     ? " 登录失败，失败原因：{ $err }，登录的账号为：" . request()->get('name') . "　密码为：" . request()->get('password')
        //     : "会员【{$this->member->name}】登录成功";

        $description = $err
            ? __('message.log.login_error', ['err' => $err, 'name' => request()->get('name'), 'password' => request()->get('password')])
            : __('message.log.login_success', ['name' => request()->get('name')]);

        $data = $this->getLogFormatter(MemberLog::LOG_TYPE_API_LOGIN, $description, $remark);
        $data['access_token'] = $token;
        MemberLog::create($data);
    }

    public function memberLogoutLogCreate($remark = '', $description = '')
    {
        $this->member = request()->user('member');
        $description = "会员【{$this->member->name}】注销账号" . ($description ? "," . $description : "");
        $data = $this->getLogFormatter(MemberLog::LOG_TYPE_API_LOGOUT, $description, $remark);
        $data['access_token'] = JWTAuth::getToken() ?? '';
        MemberLog::create($data);
    }

    public function memberTokenLogCreate($token = '')
    {
        $this->member = request()->user('member');
        $description = "会员【{$this->member->name}】更换token";
        $data = $this->getLogFormatter(MemberLog::LOG_TYPE_API_ACTION, $description);
        $data['access_token'] = $token;
        MemberLog::create($data);
    }

    public function memberTransferErrLogCreate($billno)
    {
        $this->member = request()->user('member');
        $description = "会员【{$this->member->name}】转入接口异常，订单号【" . $billno . "】";
        $data = $this->getLogFormatter(MemberLog::LOG_TYPE_TRANSFER_ERROR, $description, $billno);
        $data['status'] = MemberLog::STATUS_NOT_DEAL;
        MemberLog::create($data);
    }

    public function agentLoginLogCreate($err = '')
    {
        $this->member = request()->user('agent');
        $description = $err
            ? " 登录失败，失败原因：{ $err }，登录的账号为：" . request()->get('name') . "　密码为：" . request()->get('password')
            : "代理【{$this->member->name}】登录成功代理后台";

        $data = $this->getLogFormatter(MemberLog::LOG_TYPE_AGENT_LOGIN, $description);
        MemberLog::create($data);
    }

    public function agentLogoutLogCreate($remark = '')
    {
        $this->member = request()->user('agent');
        $description = "代理【{$this->member->name}】注销代理后台账号";
        $data = $this->getLogFormatter(MemberLog::LOG_TYPE_AGENT_LOGOUT, $description, $remark);
        MemberLog::create($data);
    }

    // 强制踢用户下线，并记录日志
    public function forceMemberOffline(Member $member)
    {
        if ($member->status != Member::STATUS_FORCE_OFF) return;

        // 修改用户的状态
        $member->update([
            'status' => Member::STATUS_ALLOW
        ]);

        // 记录日志
        $this->memberLogoutLogCreate('', '强制用户下线');
    }

    // 获取短信验证码日志 验证码 code 电话号码 phone
    public function sendSmsLogCreate($ip, $code, $phone)
    {
        $description = "记录IP为" . $ip . "的游客获取注册验证码的操作，验证码为【" . $code . "】";
        $data = $this->getLogFormatter(
            MemberLog::LOG_TYPE_MEMBER_SMS,
            $description,
            json_encode(["phone" => $phone, "code" => $code])
        );
        MemberLog::create($data);
    }

    // 获取短信验证码日志 验证码 code 电话号码 phone
    public function sendSmsLogReset($ip, $code, $phone)
    {
        $description = "记录IP为" . $ip . "的游客获取重置密码验证码的操作，验证码为【" . $code . "】";
        $data = $this->getLogFormatter(
            MemberLog::LOG_TYPE_MEMBER_RESET_SMS,
            $description,
            json_encode(["phone" => $phone, "code" => $code])
        );
        MemberLog::create($data);
    }
}
