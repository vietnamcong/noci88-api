<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Exceptions\InvalidRequestException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Services\CaptchaService;
use App\Services\MemberService;
use App\Services\MemberLogService;
use App\Services\MenuService;
use App\Services\AgentService;
use App\Services\ActivityService;

use App\Repositories\BetHistoryRepository;
use App\Repositories\LevelConfigRepository;
use App\Repositories\MemberMoneyLogRepository;
use App\Repositories\TransactionHistoryRepository;

use App\Models\SystemNotice;
use App\Models\SystemConfig;
use App\Models\Activity;
use App\Models\ActivityApply;
use App\Models\ApiGame;
use App\Models\Banner;
use App\Models\Member;
use App\Models\Agent;
use App\Models\AgentInvite;
use App\Models\AgentInviteRecord;
use App\Models\MemberBank;
use App\Models\Bank;
use App\Models\BankCard;
use App\Models\Message;
use App\Models\MemberMessage;
use App\Models\Recharge;
use App\Models\GameRecord;
use App\Models\LevelConfig;
use App\Models\QuickUrl;
use App\Models\Payment;
use App\Models\BetHistories;
use App\Models\Api;
use App\Models\MemberMoneyLog;
use App\Models\DailyBonus;
use App\Models\Drawing;
use App\Models\Favorite;

use App\Traits\ResponseTrait;
use App\Traits\CurdTrait;
use App\Traits\SBORequest;
use Illuminate\Http\Response;
use App\Handlers\FileUploadHandler;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ResponseTrait, CurdTrait, SBORequest;

	/** @var array $viewData */
    protected $viewData = [];

	protected $guard_name = "member";

	protected $app;

	protected $captchaService;
	protected $memberService;
	protected $memberLogService;
	protected $menuService;
	protected $agentService;
	protected $activityService;

	protected $betHistoryRepository;

	protected $systemNotice;
	protected $systemConfig;
	protected $activity;
	protected $activityApply;
	protected $apiGame;
	protected $banner;
	protected $member;
	protected $agent;
	protected $agentInvite;
	protected $agentInviteRecord;
	protected $memberBank;
	protected $bank;
	protected $bankCard;
	protected $message;
	protected $memberMessage;
	protected $recharge;
	protected $gameRecord;
	protected $levelConfig;
	protected $quickUrl;
	protected $payment;
	protected $betHistories;
	protected $api;
	protected $memberMoneyLog;
	protected $drawing;
	protected $model;
	
	const LANG_COMMON = 'common';
	const LANG_CN = 'zh_cn';
	const LANG_VN = 'vi';

	const LANGUAGE_ARRAY = ['en', 'th', 'vi', 'zh_cn', 'zh_hk'];
	const IMAGE_MAX_SIZE = 3 * 1024 * 1024;
	const FILE_MAX_SIZE = 3 * 1024 * 1024;

	public function __construct()
	{
		$this->app = app(Application::class);

		$this->captchaService = app(CaptchaService::class);
		$this->memberService = app(MemberService::class);
		$this->memberLogService = app(MemberLogService::class);
		$this->menuService = app(MenuService::class);
		$this->agentService = app(AgentService::class);
		$this->activityService = app(ActivityService::class);

		$this->systemNotice = app(SystemNotice::class);
		$this->systemConfig = app(SystemConfig::class);
		$this->activity = app(Activity::class);
		$this->activityApply = app(ActivityApply::class);
		$this->apiGame = app(ApiGame::class);
		$this->banner = app(Banner::class);
		$this->member = app(Member::class);
		$this->agent = app(Agent::class);
		$this->agentInvite = app(AgentInvite::class);
		$this->agentInviteRecord = app(AgentInviteRecord::class);
		$this->memberBank = app(MemberBank::class);
		$this->bank = app(Bank::class);
		$this->bankCard = app(BankCard::class);
		$this->message = app(Message::class);
		$this->memberMessage = app(MemberMessage::class);
		$this->recharge = app(Recharge::class);
		$this->gameRecord = app(GameRecord::class);
		$this->levelConfig = app(LevelConfig::class);
		$this->quickUrl = app(QuickUrl::class);
		$this->payment = app(Payment::class);
		$this->betHistories = app(BetHistories::class);
		$this->api = app(Api::class);
		$this->memberMoneyLog = app(MemberMoneyLog::class);
		$this->drawing = app(Drawing::class);
	}

	protected function guard()
	{
		return Auth::guard($this->guard_name);
	}

	public function getMember()
	{
		$member = $this->guard()->user();
		
        if(!$member) return null;

        if($member->isDemo() && !$is_allow_demo) throw new InvalidRequestException(trans('res.api.common.demo_not_allowed'));

        if($member->status == Member::STATUS_FORBIDDEN) throw new InvalidRequestException(trans('res.api.common.member_forbidden'));

        return $member;
	}

	public function createCaptcha()
	{
		return $this->captchaService->createCodeAPI('flat');
	}

	public function commonUploadImage($image, $request, $folder, $size = self::IMAGE_MAX_SIZE)
	{
		try {
			$file_name = explode('.', $image->getClientOriginalName());
			if (count($file_name) < 2) return response()->json(['messages' => __('message.image_valid')], Response::HTTP_UNPROCESSABLE_ENTITY);

			if ($image->getSize() > $size) return response()->json(['messages' => __('message.image_limit_size', ['size' => $size])], Response::HTTP_UNPROCESSABLE_ENTITY);

			$result = app(FileUploadHandler::class)->uploadImage($image, $folder, $request->get("max_width", false));
		} catch (\Exception $e) {
			return response()->json(['messages' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		if ($result['status'] === true) {
			return Arr::only($result['data'], ['file_url'])['file_url'];
		} else {
			return response()->json(['messages' => $result['message']], Response::HTTP_UNPROCESSABLE_ENTITY);
		}
	}

	public function getLangAttribute($field){
        return (trans('res.'.$field.'.field') && is_array(trans('res.'.$field.'.field')) ) ? trans('res.'.$field.'.field') : [];
    }

	public function validateRequest($data, $validateRules, $ruleMessages = [], $attributeName = [])
    {
        if(!$attributeName && $this->model){
            $attributeName = method_exists($this, 'attributeName') ? $this->attributeName($this->model) : [];
        }

        $validator = Validator::make($data, $validateRules, $ruleMessages, $attributeName);

        if ($validator->fails()) {
            $this->dealFailValidator($validator);
        }
    }

	protected function dealFailValidator($validator)
    {
        // 有错误，处理错误信息并且返回
        $errors = $validator->errors();
        $errorTips = '';
        foreach ($errors->all() as $message) {
            $errorTips = $errorTips . $message . ',';
        }
        $errorTips = substr($errorTips, 0, strlen($errorTips) - 1);
        //return $this->failed($errorTips, 422);
        throw new InvalidRequestException($errorTips, 422);
    }

	protected function setViewData($data)
    {
        $this->viewData = array_merge($this->getViewData(), (array)$data);
    }

	public function render($view = null, array $data = [], array $mergeData = [])
    {
        $area = getArea();
        $view = $area . '.' . ($view ?: (getControllerName() . '.' . getActionName()));
        $data = array_merge($data, $this->getViewData(), [
            'title' => $this->getTitle(),
        ]);

        return view($view, $data, $mergeData);
    }
}
