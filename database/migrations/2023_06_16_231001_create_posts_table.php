<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index()->unsigned();
            $table->integer('category_id')->index()->unsigned();
			$table->string('title');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->string('thumbail')->nullable();
            $table->string('tags')->nullable();
            $table->tinyInteger('status')->default(1)->nullable()->comment('0-Disabled | 1-Enabled');
			$table->tinyInteger('sort')->default(0)->nullable();
            $table->string('source')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->datetime('publish_date')->index()->nullable();
            $table->datetime('publish_time')->index()->nullable();
            $table->integer('view_count')->default(0)->nullable();
            $table->timestamps();
			$table->index(['user_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
