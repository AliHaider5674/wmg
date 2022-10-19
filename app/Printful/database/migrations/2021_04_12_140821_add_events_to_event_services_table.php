<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Service;

/**
 * Class AddEventsToEventServicesTable
 */
class AddEventsToEventServicesTable extends Migration
{
    /**
     * Events
     */
    private const EVENTS = [
        \App\Core\Services\EventService::EVENT_ITEM_ON_HOLD,
        \App\Core\Services\EventService::EVENT_ITEM_RETURNED
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $now = Carbon::now();
        $services = Service::get();
        foreach ($services as $service) {
            foreach (self::EVENTS as $event) {
                DB::table('service_events')->insert([
                    'parent_id' => $service->id,
                    'event' => $event,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('service_events')->whereIn('event', self::EVENTS)
            ->where('parent_id', $this->getParentId())
            ->delete();
    }
}
