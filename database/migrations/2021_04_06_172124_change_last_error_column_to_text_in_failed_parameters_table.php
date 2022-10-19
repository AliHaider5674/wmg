<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @SuppressWarnings(PHPMD)
 */
class ChangeLastErrorColumnToTextInFailedParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('failed_parameters', function (Blueprint $table) {
            $table->text('last_error')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('failed_parameters', function (Blueprint $table) {
            $table->string('last_error')->change();
        });
    }
}
