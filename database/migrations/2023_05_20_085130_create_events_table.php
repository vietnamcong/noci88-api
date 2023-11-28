<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('cover_image')->nullable()->comment('Ảnh bìa');
            $table->longText('content')->nullable()->comment('Mô tả');
            $table->unsignedTinyInteger('type')->default(3)->nullable()->comment('Loại event');
            $table->unsignedTinyInteger('apply_type')->default(0)->nullable()->comment('Loại đăng ký');
            $table->string('apply_url')->nullable()->comment('Địa chỉ');
            $table->text('apply_desc')->nullable()->comment('Mô tả');
            $table->timestamp('start_at')->nullable()->comment('Bắt đầu');
            $table->timestamp('end_at')->nullable()->comment('Kết thúc');
            $table->string('date_desc')->nullable()->comment('Mô tả thời gian event');
            $table->unsignedTinyInteger('is_open')->default(0)->nullable()->comment('0: On, 1: Off');
            $table->unsignedTinyInteger('is_hot')->default(1)->nullable()->comment('0: Normal, 1: Popular');
            $table->text('rule_content')->nullable()->comment('Nội quy event');
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
        Schema::dropIfExists('events');
    }
}
