<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->comment('Tên giá điểm');
			$table->unsignedDecimal('price')->default(0)->comment('Giá');
			$table->integer('point')->default(0)->comment('Điểm');
			$table->unsignedTinyInteger('type_user')->default(0)->comment('1: Member, 0: Agent');
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
        Schema::dropIfExists('point_prices');
    }
}
