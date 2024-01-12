<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublishersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('游戏标题');
            $table->string('web_pic')->default('')->comment('电脑端图片');
            $table->string('mobile_pic')->default('')->comment('手机端图片');
            $table->unsignedTinyInteger('is_open')->default(0)->comment('0上线1下线');
            $table->string('params')->default('')->comment('进入游戏参数');
            $table->string('lang_json')->default('')->comment('多语言json');
            $table->string('lang')->default('common')->comment('语言类型');
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
        Schema::dropIfExists('publishers');
    }
}
