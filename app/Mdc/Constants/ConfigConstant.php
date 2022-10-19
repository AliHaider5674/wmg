<?php
namespace App\Mdc\Constants;

/**
 *
 * Class ConfigConstant
 * @category WMG
 * @package  App\Mdc\Constants
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class ConfigConstant
{
    const MDC_STOCK_SOURCE_IDS = 'mdc.source_ids';
    //This only take affect if source ids is set.
    //When this flag is set, if a sku have source other than the ones from
    //mdc.source_ids, stock call to mdc will be ignore.
    const MDC_ALLOW_STOCK_SOURCE_OVERLAP = 'mdc.allow.source.overlap';
}
