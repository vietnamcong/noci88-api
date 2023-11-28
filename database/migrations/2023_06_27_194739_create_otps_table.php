<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('user_id')->index()->unsigned()->default(0)->nullable();
			$table->string('phone_number')->unique();
            $table->string('otp_code')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
			$table->index(['user_id', 'phone_number', 'otp_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otps');
    }
}
