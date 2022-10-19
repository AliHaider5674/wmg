<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderAddress;
use App\Models\SourceConfig;

/**
 * Seed sample data for local only
 *
 * Class DatabaseSeeder
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //Generate service
        Service::factory()->count(3)->create()->each(
            fn(Service $service) => $service->events()->saveMany(
                ServiceEvent::factory()->count(5)->make()
            )
        );

        //Generate orders
        Order::factory()->count(3)->create()->each(
            function ($order) {
                $order->addresses()->save(OrderAddress::factory()->make([
                    'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
                ]));
                $order->orderItems()->saveMany(OrderItem::factory()->count(2)->make());
            }
        );

        //Generate Source Config
        SourceConfig::factory()->create();
    }
}
