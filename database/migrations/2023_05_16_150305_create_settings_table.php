<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('key');
            $table->text('value')->nullable();
            $table->string('group')->default(0)->nullable()->comment('Nhóm cài đặt');
			$table->string('type')->default('text')->nullable()->comment('Kiểu dữ liệu: text, number, picture, file, boolean, textarea, select, editor, ...');
            $table->unsignedTinyInteger('status')->default(1)->nullable()->comment('0-Off | 1-On');
            $table->timestamps();
			$table->index(['key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
