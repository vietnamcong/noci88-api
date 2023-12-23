<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('', function () {
    return 'Api Start';
});
Route::get('storage-link', function () {
    Artisan::call('storage:link');
    return response()->json(['messages' => 'Storage Link']);
});
// Xóa cache
Route::get('clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');
    return response()->json(['messages' => 'Optimize & Config & Route & Cache is cleared']);
});

Route::get('lottery/games', 'IndexController@getLotteryList');
Route::post('game/type', 'IndexController@game_type');
Route::get('banners', 'IndexController@getBannerListByGroups');
Route::get('system/notices', 'IndexController@getSystemNotice');
Route::get('app/notices', 'IndexController@getAppNotice');
Route::get('system/configs', 'IndexController@getSystemConfig');
Route::get('system/link', 'IndexController@getCommonLink');
Route::get('abouts', 'IndexController@getAbouts');
Route::get('about/list', 'IndexController@getAboutList');
Route::get('activities', 'IndexController@getActivityList');
Route::get('activity/type', 'IndexController@getActivityType');
Route::get('activity/{activity}', 'IndexController@getActivityDetail');
Route::get('system/global', 'IndexController@getSystemGlobal');
Route::get('asides/list', 'IndexController@getAsideList');
Route::get('lottery/list', 'IndexController@lotterylist');
Route::get('lottery/hot', 'IndexController@lotteryhot');

Route::group(['prefix'=>'act'], function () {
    Route::get('/list', 'ActivityController@activity_list');
    Route::get('/apply/config', 'ActivityController@activity_apply_config');
    Route::get('/apply/result', 'ActivityController@activity_apply_result');
    Route::any('/{activity}', 'ActivityController@activity_detail');//获取活动内容
    Route::any('/apply/{activity}', 'ActivityController@activity_apply');//提交活动申请
});
Route::group(['prefix'=>'wheel'], function () {
    Route::get('/setting', 'ActivityController@wheel_setting');
    Route::post('/query', 'ActivityController@wheel_query');
    Route::post('/award', 'ActivityController@wheel_award');
});
Route::group(['prefix'=>'credit'], function () {
    Route::get('/rule', 'ActivityController@credit_rule');
    Route::get('/record', 'ActivityController@credit_record');
    Route::post('/borrow', 'ActivityController@credit_borrow');
    Route::post('/lend', 'ActivityController@credit_lend');
    Route::post('/search', 'ActivityController@credit_search');
    Route::post('/check', 'ActivityController@credit_check');    
});
Route::group(['prefix'=>'main'], function () {
    Route::get('/advertise', 'IndexController@vip1_main_advertise');
    Route::get('/hotgame', 'IndexController@vip1_main_hotgame');
    Route::get('/sport', 'IndexController@vip1_sports');
});
Route::get('language', 'IndexController@vip1_languages');
Route::get('redbag/desc', 'MemberController@get_redbag_desc');

Route::prefix("auth")->group(function () {
    Route::post('login', 'AuthController@login');
    Route::post('captcha', 'AuthController@captcha');
    Route::post('register', 'AuthController@register');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('reset_pass', 'AuthController@reset_password');

    Route::post('reg/lang', 'AuthController@get_register_lang');

    Route::post('app/login', 'AuthController@app_login');
    Route::post('app/register', 'AuthController@app_register');

    Route::post('demo', 'AuthController@demo');

    Route::post('sms', 'AuthController@send_sms');
    Route::post('sms_reset', 'AuthController@send_sms_for_reset');
    Route::post('sms_bind', 'AuthController@send_sms_for_bind');

    Route::middleware(['refresh.member'])->group(function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('me', 'AuthController@me');
        Route::post('info/update', 'AuthController@modify_info');
    });
});

Route::middleware(['refresh.member', 'jwt.auth', 'auth:api'])->group(function () {
    Route::post('member/password/modify', 'MemberController@modify_pwd');
    Route::post('member/drawing_pwd/modify', 'MemberController@modify_qk_pwd');
    Route::post('member/drawing_pwd/set', 'MemberController@set_qk_pwd');
    Route::get('member/information', 'MemberController@information');

    Route::post('agent/apply', 'MemberController@apply_agent');
    Route::post('agent/apply/status', 'MemberController@apply_agent_status');

    Route::post('recharge/online', 'MemberController@recharge_online');
    Route::post('recharge/normal', 'MemberController@recharge');
    Route::post('recharge/picture/upload', 'MemberController@recharge_payment_pic_upload');

    Route::post('recharge/list', 'MemberController@recharge_list');

    Route::get('drawing/bank', 'MemberController@drawing_bank');
    Route::post('drawing', 'MemberController@drawing');
    Route::post('drawing/list', 'MemberController@drawing_list');

    Route::any('moneylog', 'MemberController@money_log');
    Route::get('moneylog/type', 'MemberController@money_log_type');

    Route::get('deposit/bank/list', 'MemberController@deposit_bank_list');
    Route::get('payments/list', 'MemberController@recharge_payments');
    Route::get('payment/normal/list', 'MemberController@payment_list');
    Route::get('payment/online/list', 'MemberController@payment_online');
    Route::get('payment/type', 'MemberController@payment_type');

    Route::get('member/bank/type', 'MemberController@member_bank_type');
    Route::post('member/bank', 'MemberController@member_bank_create');
    Route::get('member/bank', 'MemberController@member_bank_list');
    Route::patch('member/bank/{bank}', 'MemberController@member_bank_update');
    Route::delete('member/bank/{bank}', 'MemberController@member_bank_delete');

    Route::get('fs/levels', 'MemberController@vip1_fs_levels');

    Route::get('member/message/list', 'MemberController@message_list');
    Route::post('member/message/send_list', 'MemberController@message_send_list');
    Route::post('member/message/send/{message?}', 'MemberController@message_send');

    Route::post('member/message/read', 'MemberController@message_read_state');
    Route::delete('member/message/delete', 'MemberController@message_delete');
    Route::delete('member/message/delete_all', 'MemberController@message_delete_all');

    Route::get('member/agent', 'MemberController@agent_data');
    Route::get('member/vips', 'MemberController@vip_info');

    Route::post('game/api_moneys', 'MemberController@api_moneys');
    Route::post('game/api_money', 'MemberController@apimoney_single');
    Route::post('game/change_trans', 'MemberController@change_trans');
    Route::post('game/recovery_last', 'MemberController@recoveryLast');
    Route::any('game/login', 'SelfController@login');
    Route::post('game/balance', 'SelfController@balance');
    Route::post('game/deposit', 'SelfController@deposit');
    Route::post('game/withdrawal', 'SelfController@withdrawal');
    Route::post('game/random', 'SelfController@random_game_record');
    Route::post('game/record', 'MemberController@game_record');
    Route::post('game/histories', 'MemberController@betHistories');

    Route::post('favor/add', 'MemberController@add_favorite');
    Route::post('favor/delete', 'MemberController@delete_favorite');
    Route::get('favor/list', 'MemberController@favorite_list');

    // yuebao
    Route::post('yuebao/getMemberYuebaoList', "MemberController@getMemberYuebaoList");
    Route::post('yuebao/getMemberPlans', "MemberController@getMemberPlans");
    Route::post('yuebao/buy', "MemberController@buy_plans");
    Route::post('yuebao/withdrawal', "MemberController@yuebao_drawing");
    Route::post('yuebao/history', 'MemberController@plans_history');

    Route::post('activity/redbag', 'MemberController@get_redbag');
    Route::post('redbag/log', 'MemberController@get_redbag_log');

    Route::post('dailybonus/check', 'MemberController@daily_bonus_check');
    Route::post('dailybonus/{mod}/award', 'MemberController@daily_bonus_award');
    Route::get('dailybonus/award/list', 'MemberController@daili_award_list');
    Route::get('dailybonus/money/history', 'MemberController@daily_bonus_money_history');
    Route::get('dailybonus/award/history', 'MemberController@daily_bonus_award_history');
    Route::get('dailybonus/history', 'MemberController@daily_bonus_history');

    // normal refund
    Route::get('fsnow/list', 'MemberController@fs_now_list');
    Route::post('fsnow/fetch', 'MemberController@fs_now');

    // SBO refund
    Route::get('fssbo/list', 'MemberController@fsSboList');
    Route::post('fssbo/fetch', 'MemberController@fsSbo');

    // SABA refund
    Route::get('fssbo/saba/list', 'MemberController@fsSboSabaList');
    Route::post('fssbo/saba/fetch', 'MemberController@fsSboSaba');

    // AFB refund
    Route::get('fssbo/afb/list', 'MemberController@fsSboAfbList');
    Route::post('fssbo/afb/fetch', 'MemberController@fsSboAfb');

    // BTI refund
    Route::get('fssbo/bti/list', 'MemberController@fsSboBtiList');
    Route::post('fssbo/bti/fetch', 'MemberController@fsSboBti');

    Route::get('transactions/list', 'MemberController@getTransactions');

    Route::post('team/childlist', 'TeamController@agentChildList');
    Route::get('team/child/detail', 'TeamController@teamDetail');
    Route::get('team/fdinfo', 'TeamController@getAgentFdInfo');
    Route::post('team/add', 'TeamController@createChildMember');
    Route::post('team/gamerecord', 'TeamController@getGameRecord');
    Route::post('team/moneylog', 'TeamController@getMoneyLog');
    Route::post('team/childrates', 'TeamController@modifyChildRates');
    Route::post('team/report', 'TeamController@teamReport');
    Route::post('team/chart', 'TeamController@teamChart');
    Route::get('team/performance', 'TeamController@performanceQueryTotal');
    Route::get('team/performanceDetail', 'TeamController@performanceQueryDetail');
    Route::post('team/invite/create', 'TeamController@agentInviteCreate');
    Route::post('team/invite/update', 'TeamController@agentInviteUpdate');
    Route::get('team/invite/list', 'TeamController@agentInviteList');
    Route::any('team/invite/records', 'TeamController@agentInviteRecords');
});

Route::prefix('games')->group(function () {
    Route::get('/', 'IndexController@getMainGameList');
    Route::get('/apis', 'IndexController@getGameApiList');
    Route::get('/web', 'IndexController@getAllApiGames');
    Route::get('/hotmain', 'IndexController@getMainPageHotSlotGame');
    Route::get('/lists', 'IndexController@getGameLists');
    Route::get('/slot/logos', 'IndexController@getSlotLogoList');

    Route::get('/categories', 'GamesController@categories');
    Route::get('/list', 'GamesController@index');
    Route::get('/play', 'GamesController@play');
});

// SBO seamless api
Route::post('GetBalance', 'SeamlessController@getBalance');
Route::post('Deduct', 'SeamlessController@deduct');
Route::post('Settle', 'SeamlessController@settle');
Route::post('Rollback', 'SeamlessController@rollback');
Route::post('Cancel', 'SeamlessController@cancel');
Route::post('Tip', 'SeamlessController@tip');
Route::post('Bonus', 'SeamlessController@bonus');
Route::post('ReturnStake', 'SeamlessController@returnStake');
Route::post('LiveCoinTransaction', 'SeamlessController@liveCoinTransaction');
Route::post('GetBetStatus', 'SeamlessController@getBetStatus');

// SBO api
Route::prefix('sbo')->as('api.sbo.')->group(function () {
    Route::post('/bet/detail', 'SBOController@getBetDetail');
    Route::post('/signup', 'SBOController@signupAccount');
    Route::post('/update-bet-setting', 'SBOController@signupAccount');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Eeziepay
Route::prefix('eeziepay')->as('eeziepay.')->group(function () {
    Route::get('/deposit', ['uses' => 'EeziepayController@deposit']);
    Route::get('/deposit/confirm', ['uses' => 'EeziepayController@confirm']);
    Route::get('/deposit/valid', ['uses' => 'EeziepayController@depositValid']);
    Route::get('/deposit/success', ['uses' => 'EeziepayController@success']);
    Route::get('/histories', ['uses' => 'EeziepayController@histories']);

    Route::get('/bank-code', ['uses' => 'EeziepayController@bankCode']);
    Route::get('/bank-qr-code', ['uses' => 'EeziepayController@bankQrCode']);

    Route::post('/redirect', ['uses' => 'EeziepayController@redirect'])->name('redirect');
    Route::post('/callback', ['uses' => 'EeziepayController@callback'])->name('callback');
});