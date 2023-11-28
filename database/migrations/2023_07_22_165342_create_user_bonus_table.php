<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bonus', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedTinyInteger('type_user')->default(1)->nullable()->comment('1: Admin, 2: Agent, 3: Member');
			$table->decimal('price', 16, 2)->unsigned()->default(0)->nullable()->comment('Số tiền');
			$table->decimal('bonus', 16, 2)->unsigned()->default(0)->nullable()->comment('Số tiền/phần trăm ($/%)');
			$table->unsignedTinyInteger('type_bonus')->default(0)->nullable()->comment('Nhập số tiền nếu type_bonus là 1-$, nhập % nếu type_bonus là 0-%');
            $table->string('content')->default('')->nullable()->comment('Nội dung');
            $table->unsignedTinyInteger('status')->default(1)->nullable()->comment('1: On, 0: Off');
			$table->softDeletes();
            $table->timestamps();
			$table->index(['type_user']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_bonus');
    }
}
