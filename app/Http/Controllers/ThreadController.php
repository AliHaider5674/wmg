<?php
namespace App\Http\Controllers;

use App\Models\Thread;

/**
 * Receive running threads
 *
 * Class \App\Http\ShipController
 *
 * @category WMG
 * @package  WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2018
 * @link     http://www.wmg.com
 */
class ThreadController extends Controller
{
    public function list()
    {
        try {
            $threads = Thread::get();
            return $threads->toArray();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.'
            ], 403);
        }
    }

    public function kill($id)
    {
        try {
            $thread = Thread::where('id', '=', $id)->first();
            $thread->delete();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.'
            ], 403);
        }
    }
}
