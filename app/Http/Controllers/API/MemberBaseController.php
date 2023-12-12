<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\ControllerDispatcher;

class MemberBaseController extends Controller
{
    protected $guard_name = "api";

    public function __construct()
    {
//        if(app()->getLocale() != $this->getMemberLang())
//            app()->setLocale($this->getMemberLang());

        parent::__construct();
    }

    public function guard()
    {
        return Auth::guard($this->guard_name);
    }

    public function getMember($is_allow_demo = 0){
        $member = $this->guard()->user();

        if(!$member) return null;

        if($member->isDemo() && !$is_allow_demo) throw new InvalidRequestException(trans('res.api.common.demo_not_allowed'));

        if($member->status == Member::STATUS_FORBIDDEN) throw new InvalidRequestException(trans('res.api.common.member_forbidden'));

        return $member;
    }

    public function getMemberLang(){
        return $this->getMember()->lang ?? getRequestLang();
    }

    protected function api_print($str){
        var_dump($str);exit;
    }

    public function checkRequestLimit($username,$method,$sec = 3){
        $key = $username.'_'.$method;
        $now = time();
        if($time = cache($key)){
            if($now - $time  < $sec) throw new InvalidRequestException(trans('res.api.common.operate_error'));
            else cache([$key => $now],now()->addSeconds($sec));
        }else{
            cache([$key => $now],now()->addSeconds($sec));
        }
    }

    protected function forward($controller, $action)
    {
        $container = app();
        $route = Route::current();
        $controllerInstance = $container->make($controller);
        return (new ControllerDispatcher($container))->dispatch($route, $controllerInstance, $action);
    }
}
