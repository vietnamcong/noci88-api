<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
			Schema::create('user_banks', function (Blueprint $table) {
				$table->bigIncrements('id');
				$table->integer('bank_id')->index()->unsigned();
				$table->unsignedInteger('user_id');
				$table->string('card_no', 150)->comment('Số thẻ');
				$table->unsignedTinyInteger('card_type')->default(1)->comment('Loại thẻ');
				$table->string('bank_type')->default('')->comment('Loại ngân hàng');
				$table->string('phone', 50)->nullable()->comment('Sđt dăng ký ngân hàng');
				$table->string('owner_name', 150)->comment('Chủ thẻ');
				$table->string('bank_address')->default('')->comment('Địa chỉ ngân hàng');
				$table->string('qrcode')->nullable()->comment('Hình ảnh QR code');
				$table->unsignedTinyInteger('status')->default(1)->comment('0: Off | 1: On');
				$table->softDeletes();
				$table->timestamps();
				$table->index(['user_id']);
			});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_banks');
    }
}
