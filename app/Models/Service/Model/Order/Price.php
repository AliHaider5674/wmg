<?php
namespace App\Models\Service\Model\Order;

use App\Models\Service\Model\Serialize;

/**
 * Price model that send to external services
 *
 * Class Price
 * @category WMG
 * @package  App\Models\Service\Model\Order
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Price extends Serialize
{
    /** @var float */
    public $netAmount;
    /** @var float */
    public $grossAmount;
    /** @var float */
    public $taxAmount;
    /** @var float */
    public $taxRate;
    /** @var [\App\Models\Service\Model\Shipment\Price\Tax] */
    public $taxes;
    /** @var string */
    public $currency;
}
