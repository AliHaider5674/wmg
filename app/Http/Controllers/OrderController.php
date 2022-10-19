<?php
namespace App\Http\Controllers;

use App\Models\Order;

/**
 * Receive orders that are going to be drop to warehouse
 *
 * Class \App\Http\ShipController
 *
 * @category WMG
 * @package  WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2018
 * @link     http://www.wmg.com
 */
class OrderController extends Controller
{
    public function __invoke($status = null)
    {
        try {
            $orders = Order::with('orderItems')->with('shippingAddress');
            switch (strtolower($status)) {
                case 'ready':
                    $orders->whereNull('drop_id');
                    break;
                case 'dropped':
                    $orders->whereNotNull('drop_id')
                        ->whereHas('orderItems', function ($query) {
                            $query->where('quantity_backordered', '=', 0)
                                  ->where('quantity_ack', '=', 0)
                                  ->where('quantity_shipped', '=', 0);
                        });
                    break;
                case 'acked':
                    $orders->whereNotNull('drop_id')
                        ->whereHas('orderItems', function ($query) {
                            $query->where('quantity_backordered', '=', 0)
                                ->where('quantity_ack', '>', 0)
                                ->whereRaw('quantity_shipped != quantity');
                        });
                    break;
                case 'shipped':
                    $orders->whereNotNull('drop_id')
                        ->whereHas('orderItems', function ($query) {
                            $query->whereRaw('quantity_shipped = quantity');
                        });
                    break;
            }
            return $orders->get()->toArray();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.'
            ], 403);
        }
    }
}
