<?php
namespace App\Models\Service\Model\Order;

use App\Models\Service\Model\Serialize;

/**
 * Tax model that send to external services
 *
 * Class Tax
 * @category WMG
 * @package  App\Models\Service\Model\Order
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Tax extends Serialize
{
    /** @var string */
    public $type;
    /** @var float */
    public $amount;
    /** @var float */
    public $rate;
}
