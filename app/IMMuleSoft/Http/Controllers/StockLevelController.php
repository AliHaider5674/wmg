<?php

namespace App\IMMuleSoft\Http\Controllers;

use Illuminate\Http\Request;
use App\IMMuleSoft\Constants\ResourceConstant;

/**
 * Class StockLevel
 * @package App\IMMuleSoft\Http\Controllers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class StockLevelController extends AbstractController
{
    protected string $controllerType = 'stock-level';
    protected string $resourceType = ResourceConstant::RESOURCE_TYPE_STOCK_LEVEL;

    private const QUERY_PARAM_CURRENT_BATCH = 'currentBatch';
    private const QUERY_PARAM_TOTAL_BATCHES = 'totalBatches';


    /**
     * processRequest
     * @param Request $request
     * @return array
     */
    protected function processRequest(Request $request) : array
    {
        $data = array();
        $queryParams = array();

        $queryParams[self::QUERY_PARAM_CURRENT_BATCH] = 0;
        $queryParams[self::QUERY_PARAM_TOTAL_BATCHES] = 0;

        //get query params
        if ($request->has(self::QUERY_PARAM_CURRENT_BATCH)) {
            $queryParams[self::QUERY_PARAM_CURRENT_BATCH] = $request->query(self::QUERY_PARAM_CURRENT_BATCH);
        }

        if ($request->has(self::QUERY_PARAM_TOTAL_BATCHES)) {
            $queryParams[self::QUERY_PARAM_TOTAL_BATCHES] = $request->query(self::QUERY_PARAM_TOTAL_BATCHES);
        }

        $data['requestData'] = $request->getContent();
        $data['additional'] = json_encode($queryParams);

        return $data;
    }
}
