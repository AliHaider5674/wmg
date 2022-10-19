<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

/**
 * Execute Fulfillment Jobs
 *
 * Class \App\Http\ShipController
 *
 * @category WMG
 * @package  WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2018
 * @link     http://www.wmg.com
 */
class FulfillmentController extends Controller
{
    public function run(Request $request)
    {
        try {
            $job = $request->get('job');
            $command = 'wmg:fulfillment';
            Artisan::call($command, ['type' => $job]);
            $output = Artisan::output();
            return "Job $job as been executed. " . $output;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.'
            ], 403);
        }
    }
}
