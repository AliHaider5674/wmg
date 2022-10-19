<?php
namespace App\Models\Service\Model\Order;

use App\Models\Service\Model\Serialize;

/**
 * Line promotion
 *
 * Class LinePromotion
 * @category WMG
 * @package  App\Models\Service\Model\Order
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class LinePromotion extends Serialize
{
    /** @var string */
    public $code;
    /** @var string */
    public $name;
    /** @var float */
    public $discount;
    /** @var float */
    public $percentage;
}
