<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventAppliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_applies', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('user_id')->index()->unsigned()->default(0);
			$table->integer('transfer_id')->index()->unsigned()->nullable()->default(0);
			$table->integer('event_id')->index()->unsigned()->nullable()->default(0);
            $table->string('data_content')->default('')->nullable()->comment('Nội dung');
            $table->string('coupon')->default('')->nullable()->comment('Mã khuyến mãi');
            $table->unsignedTinyInteger('status')->default(1)->nullable()->comment('1: On, 0: Off');
			$table->softDeletes();
            $table->timestamps();
			$table->index(['user_id', 'transfer_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_applies');
    }
}
