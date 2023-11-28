<?php

namespace App\Http\Controllers\API\ClientPC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;

class MemberController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function member()
    {
        $member = $this->getMember();
        $member['usdt'] = config('platform.usdt_type');
        return response()->json(['message' => __('message.success'), 'data' => $member], Response::HTTP_OK);
    }

    // Sửa đổi mật khẩu rút tiền
    public function modifyQkPwd(Request $request)
    {
        $member = $this->getMember();
        $input = $request->only(['old_qk_pwd', 'qk_pwd', 'qk_pwd_confirmation']);

        $validator = Validator::make($input, [
            "old_qk_pwd" => 'required|min:6',
            "qk_pwd" => 'required|min:6|different:old_qk_pwd',
            'qk_pwd_confirmation' => 'required|min:6|same:qk_pwd',
        ], [
            'old_qk_pwd.required' => __('message.modify_pwd.old_qk_pwd_required'),
            'old_qk_pwd.min' => __('message.modify_pwd.old_qk_pwd_min'),
            'qk_pwd.required' => __('message.modify_pwd.qk_pwd_required'),
            'qk_pwd.min' => __('message.modify_pwd.qk_pwd_min'),
            'qk_pwd.different' => __('message.modify_pwd.qk_pwd_different'),
            'qk_pwd_confirmation.required' => __('message.modify_pwd.qk_pwd_confirmation_required'),
            'qk_pwd_confirmation.min' => __('message.modify_pwd.qk_pwd_confirmation_min'),
            'qk_pwd_confirmation.same' => __('message.modify_pwd.qk_pwd_confirmation_same'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (optional($member)->qk_pwd != $input['old_qk_pwd']) {
            return response()->json(['message' => __('message.modify_pwd.qk_pwd_error')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($member->update(['qk_pwd' => $input['qk_pwd']])) {
            return response()->json(['message' => __('message.success')], Response::HTTP_OK);
        } else {
            return response()->json(['message' => __('message.error'), 'data' => $member], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // Đặt mật khẩu rút tiền
    public function setQkPwd(Request $request)
    {
        $member = $this->getMember();
        $input = $request->only('qk_pwd');

        if (optional($member)->qk_pwd) {
            return response()->json(['message' => __('message.modify_pwd.qk_pwd_set')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validator = Validator::make($input, [
            'qk_pwd' => 'required|min:6',
        ], [
            'qk_pwd.required' => __('message.modify_pwd.qk_pwd_required'),
            'qk_pwd.min' => __('message.modify_pwd.qk_pwd_min'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($member->update(['qk_pwd' => $input['qk_pwd']])) {
            return response()->json(['message' => __('message.error')], Response::HTTP_OK);
        } else {
            return response()->json(['message' => __('message.error')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function changePassword(Request $request)
    {
        $input = $request->input();

        $validator = Validator::make($input, [
            'password_old' => 'required|min:6',
            'password' => 'required|confirmed|min:6|different:password_old',
            'password_confirmation' => 'required|min:6|same:password'
        ], [
            'password_old.required' => __('message.change_password.password_old_required'),
            'password_old.min' => __('message.change_password.password_old_min'),
            'password.required' => __('message.change_password.password_required'),
            'password.confirmed' => __('message.change_password.password_confirmed'),
            'password.min' => __('message.change_password.password_min'),
            'password.different' => __('message.change_password.password_different'),
            'password_confirm.required' => __('message.change_password.password_confirm_required'),
            'password_confirm.min' => __('message.change_password.password_confirm_min'),
            'password_confirm.same' => __('message.change_password.password_confirm_same'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $member = $this->getMember();
        $hash = optional($member)->password;
        if (password_verify($input['password_old'], $hash)) {
            $member->update(['password' => $input['password']]);
            return response()->json(['message' => __('message.change_password.success')], Response::HTTP_OK);
        } else {
            return response()->json(['message' => __('message.change_password.password_old_invalid')], Response::HTTP_BAD_REQUEST);
        }
    }

    public function bankType()
    {
        $member = $this->getMember();
        $bank_type = $this->bank->getBankArray(optional($member)->lang);

        $data = [
            'bank_type' => $bank_type,
            'usdt' => config('platform.usdt_type')
        ];

        return response()->json(['message' => __('message.success'), 'data' => (object) $data], Response::HTTP_OK);
    }

    public function bank()
    {
        $member = $this->getMember();
        $bank = $this->memberBank->where('member_id', optional($member)->id)->get();
        $bank->transform(function ($item, $key) {
            $item->url = config('platform.bank_urls')[$item->bank_type] ?? '';
            return $item;
        });

        $bank_type = $this->bank->getBankArray(optional($member)->lang);

        $data = [
            'bank' => $bank,
            'bank_type' => $bank_type,
        ];

        return response()->json(['message' => __('message.success'), 'data' => (object) $data], Response::HTTP_OK);
    }

    public function bankDetail($id)
    {
        $bank = $this->memberBank->find($id);
        if (!$bank) {
            return response()->json(['message' => __('message.member_bank.bank_not_found')], Response::HTTP_NOT_FOUND);
        }

        $bank->url = config('platform.bank_urls')[$bank->bank_type] ?? '';
        return response()->json(['message' => __('message.success'), 'data' => $bank], Response::HTTP_OK);
    }

    public function bankCreate(Request $request)
    {
        $input = $request->input();
        $member = $this->getMember();

        $validator = Validator::make($input, [
            'card_no' => 'required|min:10',
            'bank_type' => ['required', Rule::in(array_keys($this->bank->getBankArray(optional($member)->lang)))],
            "owner_name" => "required",
            "bank_address" => "sometimes|required",
            "phone" => "sometimes|required",
        ], [
            'card_no.required' => __('message.member_create_bank.card_no_required'),
            'card_no.min' => __('message.member_create_bank.card_no_min'),
            'bank_type.required' => __('message.member_create_bank.bank_type_required'),
            'bank_type.in' => __('message.member_create_bank.bank_type_invalid'),
            'owner_name.required' => __('message.member_create_bank.owner_name_required'),
            'bank_address.required' => __('message.member_create_bank.bank_address_required'),
            'phone.required' => __('message.member_create_bank.phone_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $input = array_filter_null($input);
        $input['member_id'] = optional($member)->id;

        if ($result = $this->memberBank->create($input)) {
            return response()->json(['message' => __('message.member_create_bank.success'), 'data' => $result], Response::HTTP_OK);
        } else {
            return response()->json(['message' => __('message.member_create_bank.error')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function bankUpdate(Request $request, $id)
    {
        $bank = $this->memberBank->find($id);
        if (!$bank) {
            return response()->json(['message' => __('message.member_bank.bank_not_found')], Response::HTTP_NOT_FOUND);
        }

        $input = $request->only('card_no', 'bank_type', 'owner_name', 'bank_address', 'phone');
        $member = $this->getMember();

        $validator = Validator::make($input, [
            'card_no' => 'required|min:10',
            'bank_type' => ['required', Rule::in(array_keys($this->bank->getBankArray(optional($member)->lang)))],
            "owner_name" => "required",
            "bank_address" => "sometimes|required",
            "phone" => "sometimes|required",
        ], [
            'card_no.required' => __('message.member_update_bank.card_no_required'),
            'card_no.min' => __('message.member_update_bank.card_no_min'),
            'bank_type.required' => __('message.member_update_bank.bank_type_required'),
            'bank_type.in' => __('message.member_update_bank.bank_type_invalid'),
            'owner_name.required' => __('message.member_update_bank.owner_name_required'),
            'bank_address.required' => __('message.member_update_bank.bank_address_required'),
            'phone.required' => __('message.member_update_bank.phone_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $input = array_filter_null($input);
        $input['member_id'] = optional($member)->id;

        if ($this->updateByModel($bank, $input)) {
            return response()->json(['message' => __('message.member_update_bank.success')], Response::HTTP_OK);
        } else {
            return response()->json(['message' => __('message.member_update_bank.error')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function bankDelete($id)
    {
        $bank = $this->memberBank->find($id);
        if (!$bank) {
            return response()->json(['message' => __('message.member_bank.bank_not_found')], Response::HTTP_NOT_FOUND);
        }

        $bank->delete();
        return response()->json(['message' => __('message.success')], Response::HTTP_OK);
    }

    public function depositBankList()
    {
        $data = $this->bankCard->where('is_open', 1)->get();
        return response()->json(['message' => __('message.success'), 'data' => $data], Response::HTTP_OK);
    }

    public function messages(Request $request)
    {
        $member = $this->getMember();

        // Thông báo tin nhắn tại chỗ sau khi tạo thành viên
        $unread = $this->message->where('visible_type', $this->message->VISIBLE_TYPE_ALL)
            ->whereNotIn('id', $this->memberMessage->withTrashed()->where('member_id', optional($member)->id)->pluck('message_id'))
            ->where('created_at', '>', optional($member)->created_at)
            ->where('lang', optional($member)->lang)
            ->get();

        // Tạo lời nhắc tin nhắn chưa đọc
        if ($unread) {
            $member_message = [];
            foreach ($unread as $item) {
                array_push($member_message, [
                    'member_id' => optional($member)->id,
                    'message_id' => $item->id,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->created_at
                ]);
            }

            $this->memberMessage->insert($member_message);
        }

        $limit = $request->get('limit', 10);
        $mod = $this->message->query()->memberMessage(optional($member)->id)->where('messages.pid', '=', 0);
        $collection = $mod->get();
        $result = $mod->paginate($limit);

        $data = [
            'data' => $result,
            'unread' => $collection->where('is_read', 0)->count(),
            'notice' => $collection->where('send_type', $this->message->SEND_TYPE_ADMIN)->where('is_read', 0)->count(),
        ];

        return response()->json(['message' => __('message.success'), 'data' => (object) $data], Response::HTTP_OK);
    }

    public function messageDetail($id)
    {
        $message = $this->memberMessage->find($id);
        if (!$message) {
            return response()->json(['message' => __('message.member_bank.bank_not_found')], Response::HTTP_NOT_FOUND);
        }

        $message->update([
            'is_read' => 1
        ]);

        return response()->json(['message' => __('message.success'), 'data' => $message], Response::HTTP_OK);
    }

    public function messageReadState(Request $request)
    {
        $input = $request->only('ids', 'state');
        $validator = Validator::make($input, [
            'ids' => 'required',
            'state' => 'required|boolean'
        ], [
            'ids.required' => __('message.member_message_read.ids_required'),
            'state.required' => __('message.member_message_read.state_required'),
            'state.boolean' => __('message.member_message_read.state_boolean'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $input['ids'] = is_array($input['ids']) ? $input['ids'] : [$input['ids']];
        $msg = Arr::get(trans('res.option.is_read'), intval($input['state']));
        if ($this->memberMessage->whereIn('message_id', $input['ids'])->update([
            'is_read' => $input['state']
        ])) {
            return response()->json(['message' => __('message.success'), 'data' => $msg], Response::HTTP_OK);
        } else {
            return response()->json(['message' => __('message.error')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function messageDelete(Request $request)
    {
        $member = $this->getMember();
        $input = $request->only('ids', 'message', 'all');
        $ids = is_array($input['ids']) ? $input['ids'] : [$input['ids']];

        $mod = null;
        if (isset($input['all'])) {
            $mod = $this->memberMessage->where('member_id', optional($member)->id);
        } else {
            $mod = $this->memberMessage->where('member_id', optional($member)->id)->whereIn('message_id', $ids);
        }

        if (isset($input['message'])) $mod = $this->message->whereIn('id', $ids)->where('member_id', optional($member)->id);

        if ($mod != null && $mod->delete()) {
            return response()->json(['message' => __('message.success')], Response::HTTP_OK);
        } else {
            return response()->json(['message' => __('message.error')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function vipInfo()
    {
        $member = $this->getMember();

        $data = $this->levelConfig->orderBy('level')->where('lang', optional($member)->lang)->get();

        $memberLevels = $this->levelConfig->where('level', optional($member)->level)->where('lang', optional($member)->lang)->first();

        $data = [
            'levels' => $data,
            'total_bet' => $this->gameRecord->getMemberTotalValidBet(optional($member)->id),
            'total_deposit' => $this->recharge->where('member_id', optional($member)->id)->where('status', $this->recharge->STATUS_SUCCESS)->sum('money'),
            'levelup_types' => trans('res.option.levelup_types'),
            'member_levels' => [
                'level_bonus' => $memberLevels->level_bonus ?? 0,
                'day_bonus' => $memberLevels->day_bonus ?? 0,
                'week_bonus' => $memberLevels->week_bonus ?? 0,
                'month_bonus' => $memberLevels->month_bonus ?? 0,
                'year_bonus' => $memberLevels->year_bonus ?? 0,
            ]
        ];

        return response()->json(['message' => __('message.success'), 'data' => (object) $data], Response::HTTP_OK);
    }

    public function agentData()
    {
        $member = $this->getMember();
        if (!$agent = optional($member)->agent) return response()->json(['message' => __('message.agent.not_agent')], Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = [
            // 'agent_site' => route('agent.login'),
            'share_link' => $agent->getAgentUri(),
            'share_link_qrcode' => 'https://api.pwmqr.com/qrcode/create/?url=' . urlencode($agent->getAgentUri()),
            'member_count' => count($this->agentService->getChildMemberIds($member))
        ];

        return response()->json(['message' => __('message.success'), 'data' => $data], Response::HTTP_OK);
    }

    public function paymentList()
    {
        $member = $this->getMember();
        $data = $this->payment->where('is_open', 1)->langs(optional($member)->lang)->distinct()->get('type');

        $types = collect([]);
        foreach ($data->toArray() as $item) {
            $key = explode('_', $item['type'])[0];
            if (!$types->where('type', $key)->count()) {
                $first = mb_strpos($item['type_text'], '(');
                $types->push([
                    'type' => $key,
                    'type_text' => mb_substr($item['type_text'], $first + 1, mb_strlen($item['type_text']) - $first - 2)
                ]);
            }
        }

        $data = [
            'data' => $data,
            // Nhận thông tin mật
            'type' => $types
        ];

        return response()->json(['message' => __('message.success'), 'data' => (object) $data], Response::HTTP_OK);
    }

    public function paymentNormalList()
    {
        $member = $this->getMember();
        $data = $this->payment->where('is_open', 1)->where('type', 'like', $this->payment::PREFIX_COMPANY . '%')->langs(optional($member)->lang)->get();

        $data->transform(function ($item, $key) use ($member) {
            if ($item->type == $this->payment::TYPE_BANKPAY) {
                $temp = $item->params;
                $temp['bank_type_text'] = Arr::get($this->bank->getBankArray(optional($member)->lang), $item->params['bank_type'], '');
                $temp['logo'] = Arr::get($this->bank->getBank($item->params['bank_type']), 'logo', '');
                $item->params = $temp;
            } else if ($item->type == $this->payment::TYPE_USDT && !is_array($item->usdt_type_text)) {
                $temp = $item->params;
                $temp['usdt_type_text'] = $item->usdt_type_text;
                $item->params = $temp;
            }
            $item->remark_code = random_int(1000, 9999);

            return $item;
        });

        return response()->json(['message' => __('message.success'), 'data' => $data], Response::HTTP_OK);
    }

    public function paymentOnlineList()
    {
        $member = $this->getMember();
        $data = $this->payment->where('is_open', 1)->where('type', 'like', $this->payment::PREFIX_THIRDPAY . '%')->langs(optional($member)->lang)->get();

        return response()->json(['message' => __('message.success'), 'data' => $data->makeHidden('params')], Response::HTTP_OK);
    }

    public function gameType()
    {
        $sys_cp = $this->api->where('api_name', 'LY')->first();

        $data = collect(config('platform.game_type'))->map(function ($item, $key) use ($sys_cp) {
            $data = [];
            $data['key'] = $key;
            $data['value'] = ($key == 99 && $sys_cp && $sys_cp->api_title) ? $sys_cp->api_title : $item;
            $data['isLobbyPage'] = $key == 3 || $key == 6;
            return $data;
        });

        return response()->json(['message' => __('message.success'), 'data' => array_values($data->toArray())], Response::HTTP_OK);
    }

    public function gameHistories(Request $request)
    {
        $member = $this->getMember();
        $member_id = optional($member)->id;

        $api_name = $request->api_name;
        $start_date = Carbon::now()->format('Y-m-d 00:00:00');
        $end_date = Carbon::now()->format('Y-m-d 23:59:59');
        if ($request->get('time_range')) {
            $time_range = $request->time_range;
            $start_date = explode(" - ", $time_range)[0];
            $end_date = explode(" - ", $time_range)[1];
        }
        $limit = $request->get('limit', 10);

        $result = $this->betHistories
            ->where('member_id', $member_id)
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") BETWEEN ? AND ?', [$start_date, $end_date])
            ->when($api_name != null, function ($query) use ($api_name) {
                $query->where('api_name', $api_name);
            })
            ->latest()
            ->paginate($limit);

        return response()->json(['message' => __('message.success'), 'data' => $result], Response::HTTP_OK);
    }

    public function rechargeList(Request $request)
    {
        $member = $this->getMember();
        $input = $request->only(['payment_type', 'status']);
        $start_date = Carbon::now()->format('Y-m-d 00:00:00');
        $end_date = Carbon::now()->format('Y-m-d 23:59:59');
        if ($request->get('time_range')) {
            $time_range = $request->time_range;
            $start_date = explode(" - ", $time_range)[0];
            $end_date = explode(" - ", $time_range)[1];
        }
        $limit = $request->get('limit', 10);
        $member_id = optional($member)->id;

        $mod = $this->recharge
            ->where('member_id', $member_id)
            ->whereRaw('DATE_FORMAT(hk_at, "%Y-%m-%d") BETWEEN ? AND ?', [$start_date, $end_date])
            ->when($input['payment_type'] != null, function ($query) use ($input) {
                $query->where('payment_type', $input['payment_type']);
            })
            ->when($input['status'] != null, function ($query) use ($input) {
                $query->where('status', $input['status']);
            });

        $sum_money = $mod->sum('money');
        $result = $mod->latest()->paginate($limit);

        return response()->json(['message' => __('message.success'), 'data' => $result, 'sum_money' => $sum_money], Response::HTTP_OK);
    }

    public function rechargeNormal(Request $request)
    {
        $input = $request->only(['payment_type', 'payment_account', 'payment_name', 'payment_amount', 'payment_id', 'payment_pic', 'event_id']);

        $input = array_filter($input, function ($temp) {
            return strlen($temp);
        });

        $validator = Validator::make($input, [
            "payment_type" => ['required', Rule::in(array_keys(config('platform.payment_type')))],
            "payment_account" => 'required',
            "payment_name" => 'required',
            'payment_amount' => 'required|numeric|min:0|integer',
            'payment_id' => 'required',
        ], [
            'payment_type.required' => __('message.recharge_normal.payment_type_required'),
            'payment_type.in' => __('message.recharge_normal.payment_type_invalid'),
            'payment_account.required' => __('message.recharge_normal.payment_account_required'),
            'payment_name.required' => __('message.recharge_normal.payment_name_required'),
            'payment_amount.required' => __('message.recharge_normal.payment_amount_required'),
            'payment_amount.numeric' => __('message.recharge_normal.payment_amount_numeric'),
            'payment_amount.min' => __('message.recharge_normal.payment_amount_min'),
            'payment_amount.integer' => __('message.recharge_normal.payment_amount_integer'),
            'payment_id.required' => __('message.recharge_normal.payment_id_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->hasFile('payment_pic')) {
            $payment_pic = $request->file('payment_pic');

            if (is_array($payment_pic) && count($payment_pic) > 1) {
                return response()->json(['messages' => __('message.image_limit_quantity', ['quantity' => 1])], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $imageUrl = self::commonUploadImage($payment_pic, $request, 'recharge');
            $input['payment_pic'] = $imageUrl;
        }

        $payment = $this->payment->find($input['payment_id']);
        unset($input['payment_id']);
        if (!$payment->is_open) return response()->json(['messages' => __('message.recharge_normal.payment_closed')], Response::HTTP_UNPROCESSABLE_ENTITY);

        $input['money'] = str_replace(['.', ','], '', $input['payment_amount']);
        unset($input['payment_amount']);
        if (!$payment->isMoneyNoLimited()) {
            if ($input['money'] > $payment->max || $input['money'] < $payment->min) return response()->json(['messages' => __('message.recharge_normal.payment_between', ['min' => $payment->min, 'max' => $payment->max])], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $member = $this->getMember();

        $input = array_filter_null($input);
        $input['bill_no'] = getBillNo();
        $input['member_id'] = optional($member)->id;
        $input['lang'] = optional($member)->lang;
        $input['name'] = $input['payment_name'];
        unset($input['payment_name']);
        $input['account'] = $input['payment_account'];
        unset($input['payment_account']);
        $input['hk_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $payment_detail = [
            'payment_id' => $payment->id,
            'payment_account' => $payment->account,
            'payment_name' => $payment->name,
        ];
        $input['payment_detail'] = json_encode($payment_detail, JSON_UNESCAPED_UNICODE);
        $input['status'] = $this->recharge::STATUS_UNDEAL;

        $recharge = null;
        try {
            DB::transaction(function () use ($input, &$recharge) {
                $recharge = $this->recharge->create($input);

                // Create member money log
                $this->memberMoneyLog->create([
                    'member_id' => $recharge->member_id,
                    'money' => $recharge->money,
                    'money_before' => $recharge->before_money + $recharge->diff_money,
                    'money_after' => $recharge->before_money + $recharge->money + $recharge->diff_money,
                    'operate_type' => $this->memberMoneyLog::OPERATE_TYPE_RECHARGE_ACTIVITY,
                    'number_type' => $this->memberMoneyLog::MONEY_TYPE_ADD,
                    'user_id' => $recharge->user_id ?? 0,
                    'model_name' => get_class($recharge),
                    'model_id' => $recharge->id,
                    'description' => trans('message.recharge_normal.payment_request', ['money' => formatCurrencyVND($input['money'] * 1000)]),
                ]);
            });
        } catch (Exception $err) {
            Log::error(json_encode($err));
            return response()->json(['messages' => __('message.recharge_normal.error') . ' ' . $err->getMessage(), 'data' => $recharge], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $text_event = '⚡️ Khuyến mãi nạp: Không có';
        if ($recharge) {
            if (isset($input['event_id'])) {
                $event = $this->activity->find($input['event_id']);
                if ($event) {
                    $this->activityApply->create([
                        'member_id' => $recharge->member_id,
                        'user_id' => 0,
                        'activity_id' => $event->id,
                        'data_content' => '⚡️ Khuyến mãi nạp: 【 ' . $event->title . ' 】',
                        'status' => 1,
                        'remark' => $event->type_text,
                    ]);

                    $text_event = '⚡️ Khuyến mãi nạp: 【 ' . $event->title . ' 】';
                }
            }
            if ($payment->type == $this->payment::TYPE_USDT) {
                $payType = 'USDT';
                $paymentName = $payment->params['usdt_type'];
            } elseif ($input['payment_type'] == $this->payment::TYPE_BANKPAY) {
                $payType = 'Bank';
                $paymentName = $payment->params['bank_type'];
            } elseif ($input['payment_type'] == 'online_eeziepay') {
                $payType = 'Eeziepay';
                $paymentName = $payment->params['bank_type'];
            } else {
                $payType = $input['payment_type'];
                $paymentName = $payment->params['account_id'];
            }

            $total_recharge = 0;
            $total_withdrawal = 0;
            $total_profit = 0;
            $text_last_payment = 'LẦN NẠP GẦN NHẤT: ';

            $text_pic = '';
            if (!isset($input['payment_pic'])) $text_pic = 'Không có';

            $message = '➡ [YÊU CẦU NẠP TIỀN] --------- ' . formatCurrencyVND($input['money'] * 1000) . '
Tài khoản: ' . optional($member)->name . '
Nạp tiền thông qua: ' . $payment->desc . '
Từ: ' . $payment_detail['payment_name'] . ' - ' . $paymentName . ' - ' . $payment_detail['payment_account'] . '
【 Số tiền 】: ' . formatCurrencyVND($input['money'] * 1000) . '
' . $text_event . '
➕Tổng Nạp = ' . formatCurrencyVND($total_recharge) . '
➖Tổng Rút = ' . formatCurrencyVND($total_withdrawal) . '
' . $text_last_payment . '
🏆 Lỗ/Lãi: ' . formatCurrencyVND($total_profit) . '
Hình ảnh: ' . $text_pic;

            $this->activityService->sendAlertTelegram($message);
            if (isset($input['payment_pic'])) $this->activityService->sendPicTelegram($input['payment_pic']);

            return response()->json(['messages' => __('message.recharge_normal.success'), 'data' => $recharge], Response::HTTP_OK);
        } else {
            return response()->json(['messages' => __('message.recharge_normal.error'), 'data' => $recharge], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function rechargeOnline(Request $request)
    {
    }

    public function drawingList(Request $request)
    {
        $user_id = $request->get('user_id');
        $status = $request->get('status');
        $start_date = Carbon::now()->format('Y-m-d 00:00:00');
        $end_date = Carbon::now()->format('Y-m-d 23:59:59');
        if ($request->get('time_range')) {
            $time_range = $request->time_range;
            $start_date = explode(" - ", $time_range)[0];
            $end_date = explode(" - ", $time_range)[1];
        }
        $limit = $request->get('limit', 10);

        $member = $this->getMember();
        $member_id = optional($member)->id;
        $mod = $this->drawing
            ->where('member_id', $member_id)
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") BETWEEN ? AND ?', [$start_date, $end_date])
            ->when($user_id != null, function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->when($status != null, function ($query) use ($status) {
                $query->where('status', $status);
            });

        $sum_money = $mod->sum('money');
        $result = $mod->latest()->paginate($limit);

        return response()->json(['message' => __('message.success'), 'data' => $result, 'sum_money' => $sum_money], Response::HTTP_OK);
    }

    public function drawing(Request $request)
    {
        $input = $request->only(['bank_id', 'money', 'qk_pwd']);

        $validator = Validator::make($input, [
            "bank_id" => 'required|exists:member_banks,id',
            'money' => 'required|numeric|min:0|integer',
            "qk_pwd" => 'required',
        ], [
            'bank_id.required' => __('message.drawing.bank_id_required'),
            'bank_id.exists' => __('message.drawing.bank_id_exists'),
            'money.required' => __('message.drawing.money_required'),
            'money.numeric' => __('message.drawing.money_numeric'),
            'money.min' => __('message.drawing.money_min'),
            'money.integer' => __('message.drawing.money_integer'),
            'qk_pwd.required' => __('message.drawing.qk_pwd_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $member = $this->getMember();

        // Xác định số tiền rút có lớn hơn số dư hay không
        if ($input['money'] > optional($member)->money) {
            return response()->json(['message' => __('message.drawing.money_not_enough')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $bank_member = $this->memberBank->where('member_id', optional($member)->id)
            ->where('id', $input['bank_id'])->first();
        if (!$bank_member) return response()->json(['message' => __('message.drawing.bank_not_exist')], Response::HTTP_UNPROCESSABLE_ENTITY);

        $input['member_bank_info'] = json_encode(Arr::except($bank_member->toArray(), ['created_at', 'updated_at', 'member_id']));
        $input = Arr::except($input, ['bank_id']);

        // Xác định xem nó có nằm trong thời gian rút tiền hay không
        $start_at = systemconfig('transfer_start', self::LANG_COMMON);
        $end_at = systemconfig('transfer_end', self::LANG_COMMON);

        if (!checkIsBetweenTime($start_at, $end_at)) {
            return response()->json(['message' => __('message.drawing.time_not_allow')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Xác định xem số tiền rút có nằm trong phạm vi không
        $money_size_config = json_decode(systemconfig('drawing_money_size_json'), true);
        $min_money = $money_size_config[optional($member)->lang]['b'][0] ?? 0;
        $max_money = $money_size_config[optional($member)->lang]['b'][1] ?? 0;

        if ($input['money'] < $min_money) {
            return response()->json(['message' => __('message.drawing.min_money', ['min' => $min_money])], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($input['money'] > $max_money) {
            return response()->json(['message' => __('message.drawing.max_money', ['max' => $max_money])], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Xác định xem có phí xử lý hay không

        // Xác định xem mật khẩu rút tiền có được nhập chính xác hay không
        if ($input['qk_pwd'] != optional($member)->qk_pwd) {
            return response()->json(['message' => __('message.drawing.qk_pwd_error')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Xác định xem số lần rút tiền có vượt quá giới hạn hay không
        if ($drawing_times = systemconfig('drawing_times_per_day', self::LANG_COMMON)) {
            // Nhận số lượng đơn rút tiền hôm nay
            if ($this->drawing->where('member_id', optional($member)->id)->whereDate('created_at', Carbon::today())->count() >= $drawing_times) {
                return response()->json(['message' => __('message.drawing.times_not_enough')], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $input = Arr::except($input, 'qk_pwd');

        $input = array_filter_null($input);
        $money = $input['money'];

        // Số tiền trước khi khấu trừ
        $money_before = optional($member)->money;

        $input['bill_no'] = getBillNo();
        $input['member_id'] = optional($member)->id;
        $input['name'] = $bank_member['owner_name'];
        $input['account'] = $bank_member['card_no'];
        $input['before_money'] = $money_before;
        $input['after_money'] = $money_before - $money;
        $input['status'] = $this->drawing::STATUS_UNDEAL;

        $count_fee = 0;
        // Nếu còn mã thì money là số tiền đăng ký, input['money'] là số tiền rút thực tế
        $ml_drawing_percent = systemconfig('ml_drawing_percent', self::LANG_COMMON);
        if (optional($member)->ml_money > 0 && $ml_drawing_percent) {
            $count_fee = $money * $ml_drawing_percent / 100;
            $input['counter_fee'] = $count_fee;
            $input['money'] = $money - $count_fee;
        }

        // Xác định xem có số tiền rút lặp lại trong vòng ba giây hay không
        if ($this->drawing->where('id', optional($member)->id)->where('created_at', '>', Carbon::now()->subSeconds(3))->exists()) {
            return response()->json(['message' => __('message.operate_error')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $drawing = null;
        try {
            DB::transaction(function () use ($input, $member, $money_before, $money, $count_fee, &$drawing) {
                $message = $count_fee ? __('message.drawing.field.counter_fee', [
                    'ml_money' => optional($member)->ml_money,
                    'count_fee' => $count_fee
                ], optional($member)->lang) : '';

                $member->decrement('money', $money);
                $drawing = $this->drawing->create($input);

                // Create member money log
                $this->memberMoneyLog->create([
                    'member_id' => optional($member)->id,
                    'money' => $money,
                    'money_before' => $money_before,
                    'money_after' => $money_before - $money,
                    'number_type' => $this->memberMoneyLog::MONEY_TYPE_SUB,
                    'operate_type' => $this->memberMoneyLog::OPERATE_TYPE_WITHDRAWAL_ACTIVITY,
                    'description' => trans('message.drawing.drawing_request', ['money' => formatCurrencyVND($input['money'] * 1000)]) . $message,
                    'model_name' => get_class($drawing),
                    'model_id' => $drawing->id,
                    'user_id' => $drawing->user_id ?? 0,
                ]);
            });
        } catch (Exception $err) {
            Log::error(json_encode($err));
            return response()->json(['messages' => __('message.drawing.error') . ' ' . $err->getMessage(), 'data' => $drawing], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($drawing) {
            $total_recharge = 0;
            $total_withdrawal = 0;
            $total_profit = 0;
            $text_last_payment = 'LẦN RÚT GẦN NHẤT: ';

            $message = '⬅️ [YÊU CẦU RÚT TIỀN] --------- ' . formatCurrencyVND($input['money'] * 1000) . '
Tài khoản: ' . optional($member)->name . '
Về: ' . $bank_member['owner_name'] . ' - ' . $bank_member['bank_type'] . ' - ' . $bank_member['card_no'] . '
【 Số tiền 】: ' . formatCurrencyVND($input['money'] * 1000) . '
➕Tổng Nạp = ' . formatCurrencyVND($total_recharge) . '
➖Tổng Rút = ' . formatCurrencyVND($total_withdrawal) . '
' . $text_last_payment . '
🏆 Lỗ/Lãi: ' . formatCurrencyVND($total_profit);

            $this->activityService->sendAlertTelegram($message);

            return response()->json(['messages' => __('message.drawing.success'), 'data' => $drawing], Response::HTTP_OK);
        } else {
            return response()->json(['messages' => __('message.drawing.error'), 'data' => $drawing], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
