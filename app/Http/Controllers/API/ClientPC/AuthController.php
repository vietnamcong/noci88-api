<?php

namespace App\Http\Controllers\API\ClientPC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function login(Request $request)
    {
        $input = $request->input();

        $rule = [
            $this->username() => 'required|min:6|max:20',
            'password' => 'required|min:6',
        ];

        if (Arr::get($input, 'captcha') && Arr::get($input, 'key')) {
            $rule['key'] = 'sometimes|required|string';
            $rule['captcha'] = 'sometimes|required|captcha_api:' . $input['key'] . ',flat';
        }

        $validator = Validator::make($input, $rule, [
            $this->username() . '.required' => __('message.login.username_required'),
            $this->username() . '.min' => __('message.login.username_min'),
            $this->username() . '.max' => __('message.login.username_max'),
            'password.required' => __('message.login.password_required'),
            'password.min' => __('message.login.password_min'),
            'key.required' => __('message.login.key_required'),
            'key.string' => __('message.login.key_string'),
            'captcha.required' => __('message.login.captcha_required'),
            'captcha.captcha_api' => __('message.login.captcha_api'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (Arr::get($input, 'captcha') && Arr::get($input, 'key')) {
            $this->captchaService->captchaCheckAPI($input['captcha'], $input['key']);
        }

        $credentials = $request->only($this->username(), 'password');
        $time = time();
        $customClaims = [$this->member->CUSTOM_CLAIMS_LOGIN_TIME => $time];

        if (!$token = $this->guard()->claims($customClaims)->attempt($credentials)) {
            $this->memberLogService->memberLoginLogCreate(__('message.login.error'));
            return response()->json(['message' => __('message.login.error')], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->guard()->user()) return response()->json(['message' => __('message.member.not_found')], Response::HTTP_FOUND);
        if ($this->guard()->user()->status == -1) return response()->json(['message' => __('message.member.status_forbidden')], Response::HTTP_UNPROCESSABLE_ENTITY);
        if ($this->guard()->user()->status == -2) return response()->json(['message' => __('message.member.status_force_off')], Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->memberLogService->setIp($request->get('ip'))->memberLoginLogCreate('', $token, $time);
        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $input = array_filter($request->input(), function ($temp) {
            return strlen($temp);
        });

        $rule = [
            $this->username() => 'required|min:6|max:20|unique:members,name|regex:/^[a-zA-Z][a-zA-Z0-9]*$/',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|min:6|same:password',
            'realname' => 'sometimes|required|min:2|max:50',
            'qk_pwd' => 'required|min:6|numeric',
            'phone' => 'required|numeric|min:10',
            'lang' => ['required', Rule::in(self::LANGUAGE_ARRAY)],
        ];

        if (Arr::get($input, 'captcha') && Arr::get($input, 'key')) {
            $rule['key'] = 'sometimes|required|string';
            $rule['captcha'] = 'sometimes|required|captcha_api:' . $input['key'] . ',flat';
        }

        $validator = Validator::make($input, $rule, [
            $this->username() . '.required' => __('message.register.username_required'),
            $this->username() . '.min' => __('message.register.username_min'),
            $this->username() . '.max' => __('message.register.username_max'),
            $this->username() . '.unique' => __('message.register.username_unique'),
            $this->username() . '.regex' => __('message.register.username_regex'),
            'password.required' => __('message.register.password_required'),
            'password.min' => __('message.register.password_min'),
            'password_confirmation.required' => __('message.register.password_confirmation_required'),
            'password_confirmation.min' => __('message.register.password_confirmation_min'),
            'password_confirmation.same' => __('message.register.password_confirmation_same'),
            'phone.required' => __('message.register.phone_required'),
            'phone.numeric' => __('message.register.phone_numeric'),
            'phone.min' => __('message.register.phone_min'),
            'qk_pwd.required' => __('message.register.qk_pwd_required'),
            'qk_pwd.min' => __('message.register.qk_pwd_min'),
            'qk_pwd.numeric' => __('message.register.qk_pwd_numeric'),
            'realname.required' => __('message.register.realname_required'),
            'realname.min' => __('message.register.realname_min'),
            'realname.max' => __('message.register.realname_max'),
            'lang.required' => __('message.register.lang_required'),
            'lang.in' => __('message.register.lang_invalid'),
            'key.required' => __('message.register.key_required'),
            'key.string' => __('message.register.key_string'),
            'captcha.required' => __('message.register.captcha_required'),
            'captcha.captcha_api' => __('message.register.captcha_api'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (Arr::get($input, 'captcha') && Arr::get($input, 'key')) {
            $this->captchaService->captchaCheckAPI($input['captcha'], $input['key']);
        }

        $agent = null;
        if (isset($input['invite_code'])) {
            $agent = $this->member->getAgent($input['invite_code']);
            if ($agent) {
                $input['agent_id'] = $agent->id;
                $input['top_id'] = optional($agent)->agent_id;
            }
        }

        try {
            $member = null;
            DB::transaction(function () use ($input, $agent, &$member) {
                $input['invite_code'] = '';
                $input['is_trans_on'] = 0;
                $member = $this->member->create($input);
                if (request()->get('ip')) {
                    $member->update([
                        'register_ip' => request()->get('ip')
                    ]);
                }

                if ($agent) {
                    $this->agentInviteRecord->create(['member_id' => $agent->id, 'invite_id' => $member->id]);
                }
            });

            if ($member) {
                return $this->login(new Request([$this->username() => $member->{$this->username()}, 'password' => $input['password']]));
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout()
    {
        try {
            $this->guard()->logout();
            return response()->json(['message' => __('messsage.logout.success')], Response::HTTP_OK);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function refresh()
    {
        try {
            $token = $this->guard()->refresh();
        } catch (JWTException $e) {
            return response()->json([
                'message' => __('message.refresh_token.unauthorized'),
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        // $expires_time = $this->guard()->factory()->getTTL() * 60 * 24;
        $expires_time = 60 * 60 * 24;

        return response()->json([
            'message' => __('message.success'),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expires_time,
            'expires_at' => Carbon::createFromTimestamp($expires_time + time())->toDateTimeString()
        ], Response::HTTP_OK);
    }

    public function username()
    {
        return 'name';
    }
}
