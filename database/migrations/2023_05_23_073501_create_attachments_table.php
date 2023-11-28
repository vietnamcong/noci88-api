<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		$columnNames = config('permission.column_names');

        Schema::create('attachments', function (Blueprint $table) use($columnNames) {
            $table->bigIncrements('id');
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
			$table->ipAddress('ip')->default('');
            $table->string('original_name')->default('')->comment('Original name');
            $table->string('mime_type')->default('');
            $table->enum('file_type', ['pic', 'file', 'video'])->default('pic');
            $table->string('size')->default('0')->comment('size/kb');
            $table->string('category')->default('tmp');
			$table->string('domain')->default('');
            $table->string('storage_path')->default('')->comment('./storage/app/public/uploads/');
            $table->string('link_path')->default('')->comment('/storage/uploads/');
            $table->string('storage_name')->default('')->comment('Storage name');
            $table->timestamps();
            $table->softDeletes();
            $table->index([$columnNames['model_morph_key'], 'model_type']);
            $table->index('file_type');
            $table->index('category');
        });	
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
