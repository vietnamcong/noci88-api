<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_points', function (Blueprint $table) {
			$table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('game_id');
			$table->integer('game_point')->default(0)->nullable();
            $table->unsignedInteger('game_transfer_id');
			$table->integer('game_transfer_point')->default(0)->nullable();
            $table->string('transfer_code')->nullable();
            $table->integer('point')->default(0);
			$table->unsignedTinyInteger('status')->default(0)->nullable()->comment('0: Chờ duyệt, 1: Đã duyệt, 2: Đã huỷ');
            $table->softDeletes();
            $table->timestamps();
			$table->index(['user_id', 'game_id', 'game_transfer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer_points');
    }
}
