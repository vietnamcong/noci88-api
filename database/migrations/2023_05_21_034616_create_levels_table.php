<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->unsignedInteger('level')->comment('Vip level');
            $table->string('level_name')->default('')->comment('Tên level');
			$table->integer('withdrawal_today')->default(1)->nullable()->comment('Number of withdrawals per day');
            $table->unsignedDecimal('deposit_money', 16, 2)->default(0.00)->nullable()->comment('Deposit amount required for promotion');
            $table->unsignedDecimal('level_bonus', 16, 2)->default(0.00)->nullable()->comment('Promotion bonus');
            $table->unsignedDecimal('day_bonus', 16, 2)->default(0.00)->nullable()->comment('Daily bonus');
            $table->unsignedDecimal('week_bonus', 16, 2)->default(0.00)->nullable()->comment('Weekly bonus');
            $table->unsignedDecimal('month_bonus', 16, 2)->default(0.00)->nullable()->comment('Monthly bonus');
            $table->unsignedDecimal('year_bonus', 16, 2)->default(0.00)->nullable()->comment('Annual cash');
            $table->unsignedDecimal('credit_bonus', 16, 2)->default(0.00)->nullable()->comment('Borrowing Amount Rewards');
            $table->unsignedTinyInteger('levelup_type')->default(0)->nullable()->comment('Promotion condition type: 0 Deposit amount reaches the standard, 1 Betting amount reaches the standard, 2 All reach the standard, 3 Any one reaches the standard');
			$table->string('image')->nullable()->default(null);
            $table->timestamps();
            $table->unique(['level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('levels');
    }
}
