<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('pid')->index()->unsigned()->default(0)->nullable();
			$table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->comment('1: Chính sách, 2: Hỗ trợ, 3: Đại lý, 4: Other');;
            $table->string('image')->nullable();
            $table->string('thumbail')->nullable();
            $table->string('description')->nullable();
            $table->longText('content')->nullable();
            $table->tinyInteger('status')->default(1)->nullable()->comment('0-Disabled | 1-Enabled');
            $table->tinyInteger('sort')->default(0)->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
			$table->tinyInteger('group_type')->default(3)->nullable()->comment('2-Agent | 3-Member');
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
        Schema::dropIfExists('pages');
    }
}
