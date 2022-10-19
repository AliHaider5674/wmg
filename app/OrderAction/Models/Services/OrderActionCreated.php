<?php
namespace App\OrderAction\Models\Services;

use App\Models\Service\Model\Serialize;

/**
 * Order action create model for event dispatch
 *
 * Class OrderActionCreated
 * @category WMG
 * @package  App\OrderAction\Models\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderActionCreated extends Serialize
{
    public $orderId;
    public $salesChannel;
    public $action;
    public function setHiddenDetail($detail)
    {
        return $this->setHiddenData('detail', $detail);
    }

    public function getHiddenDetail()
    {
        return $this->getHiddenData('detail');
    }
}
