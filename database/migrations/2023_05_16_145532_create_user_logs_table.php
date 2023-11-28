<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index()->unsigned()->default(0);
            $table->ipAddress('ip')->default('')->nullable();
            $table->string('action')->default('')->nullable();
            $table->string('url')->default('')->nullable();
            $table->text('data')->nullable();
            $table->string('address')->default('')->nullable();
            $table->string('ua')->default('')->nullable();
            $table->unsignedTinyInteger('type')->nullable()->comment('1: Admin, 2: Agency, 3: Client');
            $table->string('description')->default('')->nullable();
            $table->string('other')->default('')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('user_logs');
    }
}
