<?php
namespace App\Models\Service\Model\Order;

use App\Models\Service\Model\Serialize;

/**
 * Line Promotion information model that for external services
 *
 * Class LinePromotionsInfo
 * @category WMG
 * @package  App\Models\Service\Model\Order
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class LinePromotionsInfo extends Serialize
{
    /** @var float */
    public $originalPrice;
    /** @var [\App\Models\Service\Model\Order\LinePromotion] */
    public $promotions = [];
}
