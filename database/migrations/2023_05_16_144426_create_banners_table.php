<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('banners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('url')->nullable();
            $table->string('groups')->nullable();
            $table->string('dimensions')->nullable()->comment('Chiều rộng - chiều cao');
            $table->string('jump_link')->nullable()->comment('Link liên kết');
            $table->unsignedTinyInteger('is_new_window')->default(0)->nullable()->comment('Mở cửa sổ mới');
            $table->unsignedTinyInteger('status')->default(1)->nullable()->comment('1: On, 0: Off');
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
        Schema::dropIfExists('banners');
    }
}
