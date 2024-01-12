<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublisherIdToGameListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_lists', function (Blueprint $table) {
            $table->unsignedDecimal('publisher_id', 16, 2)->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_lists', function (Blueprint $table) {
            $table->dropColumn('publisher_id');
        });
    }
}
