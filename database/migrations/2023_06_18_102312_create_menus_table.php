<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('pid')->index()->unsigned()->default(0)->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
			$table->string('thumbail')->nullable();
            $table->string('image')->nullable();
            $table->string('description')->nullable();
			$table->longText('content')->nullable();
			$table->string('tags')->nullable();
            $table->tinyInteger('status')->default(1)->nullable()->comment('0-Disabled | 1-Enabled');
            $table->tinyInteger('sort')->default(0)->nullable()->comment('Thứ tự hiển thị, default: 0');
			$table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->timestamps();
			$table->index(['pid', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
}
