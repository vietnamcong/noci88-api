<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('username')->unique()->comment('Tên đăng nhập');
			$table->string('invite_code', 100)->unique()->comment('Mã giới thiệu');
			$table->string('password');
			$table->string('original_password')->nullable()->comment('Mật khẩu thô (mật khẩu được sử dụng làm api)');
			$table->string('o_password')->nullable()->comment('Mật khẩu đăng nhập');
			$table->string('nick_name')->nullable()->comment('Tên nick');
			$table->string('real_name', 50)->nullable()->comment('Tên thật');
			$table->unsignedTinyInteger('type_user')->default(3)->nullable()->comment('Admin: 1, Agent: 2, Member: 3');
			$table->string('avatar')->nullable()->comment('Avatar');
			$table->date('birthday')->nullable()->comment('Sinh nhật');
			$table->string('email')->default('')->nullable();
			$table->string('mobile')->default('')->nullable();
			$table->string('zalo')->default('')->nullable();
			$table->string('telegram')->default('')->nullable();
			$table->string('viber')->default('')->nullable();
			$table->unsignedTinyInteger('gender')->default(0)->nullable()->comment('Giới tính: 0 - Nam  | 1 - Nữ');
			$table->unsignedTinyInteger('is_active')->default(1)->nullable()->comment('On: 1, Block: 0');
			$table->unsignedTinyInteger('status')->default(1)->nullable()->comment('0: Disable, 1: Enable');
			$table->unsignedTinyInteger('is_tips_on')->default(0)->nullable()->comment('Nhắc đăng nhập');
			$table->unsignedTinyInteger('is_in_on')->default(0)->nullable()->comment('Tài khoản nội bộ');
			$table->unsignedTinyInteger('is_trans_on')->default(0)->nullable()->comment('Chuyển tự động');
			$table->unsignedTinyInteger('is_trans_demo')->default(0)->nullable()->comment('Thử nghiệm tài khoản');
			$table->string('lang')->default('vi')->nullable()->comment('Ngôn ngữ');
			$table->unsignedInteger('top_id')->default(0)->nullable()->comment('Id đại lý của đại lý cấp trên');
			$table->unsignedInteger('agent_id')->default(0)->nullable()->comment('Id đại lý');
			$table->unsignedDecimal('total_credit', 16, 2)->default(0.00)->nullable()->comment('Tổng số tiền có thể vay');
			$table->unsignedDecimal('used_credit', 16, 2)->default(0.00)->nullable()->comment('Số tiền đã vay');
			$table->unsignedDecimal('point_price', 16, 2)->default(0.00)->comment('Giá điểm');
			$table->unsignedDecimal('score', 16, 2)->default(0)->nullable();
			$table->decimal('money', 16, 2)->default(0)->nullable()->comment('Số dư tài khoản');
			$table->unsignedDecimal('fs_money', 16, 2)->default(0)->nullable()->comment('Số dư tài khoản chiết khấu');
			$table->unsignedDecimal('ml_money', 16, 2)->default(0)->nullable()->comment('Số dư');
			$table->unsignedDecimal('total_money', 16, 2)->default(0)->nullable()->comment('Tổng tiền đặt cược');
			$table->unsignedDecimal('old_money', 16, 2)->default(0)->nullable()->comment('Tổng tiền');
			$table->unsignedDecimal('bonus_rate', 16, 2)->default(0)->nullable()->comment('Tỷ lệ bonus');
			$table->unsignedTinyInteger('is_withdraw')->default(1)->nullable()->comment('1: True, 0: False');
			$table->string('qk_pwd', 100)->nullable()->comment('Mã rút tiền');
			$table->text('image_other')->nullable()->comment('Hình ảnh khác: CCCD, GPLX,...');
			$table->unsignedInteger('level')->default(0)->nullable();
			$table->string('level_name')->default('')->nullable();
			$table->timestamp('email_verified_at')->nullable();
			$table->rememberToken();
			$table->timestamps();
			$table->softDeletes();
			$table->index(['invite_code', 'username']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users');
	}
}
