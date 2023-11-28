<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->default('')->unique()->comment('Mã ngân hàng: ACB, Techcombank, Vietcombank,..');
            $table->string('name')->default('');
            $table->string('url')->default('')->nullable()->comment('Trang web ngân hàng');
            $table->string('image')->default('')->nullable()->comment('Hình ảnh');
            $table->string('thumbail')->default('')->nullable()->comment('Ảnh đại diện');
			$table->unsignedTinyInteger('status')->default(1)->nullable()->comment('1: On, 0: Off');
			$table->unsignedInteger('sort')->default(1)->nullable()->comment('Sắp xếp: 1, 2, 3,.. Số nhỏ hiển thị sau');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banks');
    }
}
