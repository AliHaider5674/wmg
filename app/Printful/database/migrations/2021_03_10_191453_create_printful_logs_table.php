<?php declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreatePrintfulLogsTable
 * @package App\Printful
 */
class CreatePrintfulLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('printful_logs', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id');
            $table->longText('event_output')->nullable();
            $table->boolean('success');
            $table->timestamps();
        });

        Schema::table('printful_logs', static function ($table) {
            $table->foreign('event_id')
                ->references('id')
                ->on('printful_events')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('printful_logs');
    }
}
