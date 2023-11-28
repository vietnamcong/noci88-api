<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->comment('Tên game');
            $table->string('subtitle')->nullable()->comment('Phụ đề');
            $table->string('web_pic')->nullable()->comment('Hình ảnh desktop');
            $table->string('mobile_pic')->nullable()->comment('Hình ảnh mobile');
            $table->string('logo_url')->nullable()->comment('Logo desktop');
            $table->unsignedTinyInteger('game_type')->nullable()->default(1)->comment("Loại game - 1: 'Live casino', 2: 'Bắn cá', 3: 'Điện tử', 4: 'Xổ số', 5: 'Thể thao', 6: 'Game bài', 7: 'SBO', 99: 'Khác'");
            $table->string('params')->nullable()->comment('Thông số game');
            $table->unsignedTinyInteger('status')->nullable()->default(1)->comment('1: On | 0: Off');
            $table->unsignedTinyInteger('client_type')->nullable()->default(0)->comment('0: Full, 1: Desktop, 2: Mobile');
            $table->string('tags')->nullable()->comment('Nhãn game');
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
        Schema::dropIfExists('games');
    }
}
