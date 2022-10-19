<?php
use Illuminate\Database\Migrations\Migration;
use App\Models\Service;
use Illuminate\Support\Facades\Crypt;

/**
 * Class AddStatusAndDropIdColumnsToOrderItemsTable
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class UpgradeServiceAdditionToEncrypt extends Migration
{
    private const UPGRADE_VERSION = '1';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $allService = Service::get();
        foreach ($allService as $service) {
            $additon = $service->getAddition();
            $service->setAddition($additon, self::UPGRADE_VERSION);
            $service->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $allService = Service::get();
        foreach ($allService as $service) {
            $additon = json_decode($service->getAttribute('addition'), true);
            if ($additon['version'] !== self::UPGRADE_VERSION) {
                continue;
            }

            $service->setAddition(json_decode(Crypt::decrypt($additon['data']), true), null);
            $service->save();
        }
    }
}
