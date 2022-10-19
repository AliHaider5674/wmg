<?php
namespace App\Models\Service\Event\RequestData;

use App\Models\Service\Model\Serialize;

/**
 * A request object that request body
 *
 * Class ClientRequest
 * @category WMG
 * @package  App\Mdc\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class StandardRequest implements RequestDataInterface
{
    /** @var string */
    public $data;
    public function __construct(Serialize $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
