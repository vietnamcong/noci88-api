<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
			$table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('bank_id');
            $table->unsignedInteger('game_id')->default(0)->nullable();
            $table->unsignedInteger('event_id')->default(0)->nullable();
            $table->string('bill_no')->nullable();
            $table->unsignedTinyInteger('transfer_type')->default(0)->comment('0: Chuyển vào | 1: Rút ra');
            $table->unsignedDecimal('money',16,2)->default(0)->nullable()->comment('Số tiền');
            $table->integer('point')->default(0)->comment('Điểm quy đổi');
            $table->unsignedDecimal('diff_money', 16,2)->default(0)->nullable()->comment('Số tiền chênh lệch');
            $table->unsignedDecimal('real_money', 16,2)->default(0)->nullable()->comment('Số tiền chuyển đổi thực tế');
            $table->unsignedDecimal('before_money', 16,2)->default(0)->nullable()->comment('Số tiền trước khi chuyển');
            $table->unsignedDecimal('after_money', 16,2)->default(0)->nullable()->comment('Số tiền sau khi chuyển');
            $table->string('money_type')->default('money')->nullable()->comment('Trường: money | fs_money');
            $table->text('result')->nullable();
			$table->unsignedTinyInteger('status')->default(0)->nullable()->comment('0: Chờ duyệt, 1: Đã duyệt, 2: Đã huỷ');
			$table->text('images')->nullable()->comment('Hình ảnh nạp tiền');
			$table->softDeletes();
            $table->timestamps();
			$table->index(['user_id', 'bank_id', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfers');
    }
}
