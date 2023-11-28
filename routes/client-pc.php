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

Route::pattern('id', '([0-9]*)');
Route::pattern('slug', '(.*)');

// Xóa cache
Route::get('clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');
    return response()->json(['messages' => 'Optimize & Config & Route & Cache is cleared']);
});

Route::group(['middleware' => ['cors', 'language']], function () {
    // Captcha
    Route::get('captcha', 'HomeController@captcha');
    // Ngôn ngữ
    Route::get('language', 'HomeController@language');
    // Trang chủ
    Route::get('/', 'HomeController@index');
    // Đăng ký và đăng nhập
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    // Loại khuyến mãi
    Route::get('activity/type', 'HomeController@activityType');
    // Khuyến mãi
    Route::get('activities', 'HomeController@activityList');
    // Chi tiết khuyênns mãi
    Route::get('activity/{activity}', 'HomeController@activityDetail');
    // Member đã đăng nhập
    Route::group(['middleware' => ['member', 'auth.jwt']], function () {
        /*---- Member ----*/
        // Thông tin member
        Route::get('member', 'MemberController@member');
        // Cập nhật mật khẩu rút tiền
        Route::post('member/drawing_pwd/modify', 'MemberController@modifyQkPwd');
        // Set mật khẩu rút tiền
        Route::post('member/drawing_pwd/set', 'MemberController@setQkPwd');
        // Cập nhật mật khẩu đăng nhập
        Route::post('member/change-password', 'MemberController@changePassword');
        // Refresh token
        Route::post('refresh-token', 'AuthController@refresh');
        // Đăng xuất
        Route::post('logout', 'AuthController@logout');
        // Danh sách loại ngân hàng & usdt
        Route::get('member/bank/type', 'MemberController@bankType');
        // Ngân hàng member
        Route::get('member/bank', 'MemberController@bank');
        // Chi tiết ngân hàng
        Route::get('member/bank/{id}', 'MemberController@bankDetail');
        // Tạo ngân hàng member
        Route::post('member/bank', 'MemberController@bankCreate');
        // Cập nhật ngân hàng member
        Route::patch('member/bank/{bank}', 'MemberController@bankUpdate');
        // Xóa ngân hàng member
        Route::delete('member/bank/{bank}', 'MemberController@bankDelete');
        // Thông báo
        Route::get('member/messages', 'MemberController@messages');
        // Chi tiết thông báo
        Route::get('member/message/{id}', 'MemberController@messageDetail');
        // Đọc thông báo
        Route::post('member/message/read', 'MemberController@messageReadState');
        // Xóa thông báo
        Route::delete('member/message/delete', 'MemberController@messageDelete');
        // Cấp bậc
        Route::get('member/vips', 'MemberController@vipInfo');
        // Đại lý cấp dưới
        Route::get('member/agent', 'MemberController@agentData');
        Route::get('game/type', 'MemberController@gameType');
        // Lịch sử cược
        Route::get('bet/histories', 'MemberController@gameHistories');
        // Danh sách ngân hàng thụ hưởng
        Route::get('deposit/bank/list', 'MemberController@depositBankList');
        // Phương thức gửi tiền
        Route::get('payment/list', 'MemberController@paymentList');
        // Danh sách tiền gửi của công ty
        Route::get('payment/normal/list', 'MemberController@paymentNormalList');
        // Danh sách thanh toán trực tuyến
        Route::get('payment/online/list', 'MemberController@paymentOnlineList');
        // Danh sách nạp tiền
        Route::get('recharge/list', 'MemberController@rechargeList');
        // Nạp tiền trực tuyến
        Route::post('recharge/online', 'MemberController@rechargeOnline');
        // Nạp tiền gửi
        Route::post('recharge/normal', 'MemberController@rechargeNormal');
        // Danh sách rút tiền
        Route::get('drawing/list', 'MemberController@drawingList');
        // Rút tiền
        Route::post('drawing', 'MemberController@drawing');
    });
});