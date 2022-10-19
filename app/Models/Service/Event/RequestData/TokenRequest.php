<?php
namespace App\Models\Service\Event\RequestData;

use App\Models\Service\Model\Serialize;

/**
 * A request object that store token and request body
 *
 * Class ClientRequest
 * @category WMG
 * @package  App\Mdc\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class TokenRequest implements RequestDataInterface
{
    /** @var string */
    public $token;
    public $data;
    public function __construct(string $token, Serialize $data)
    {
        $this->token = $token;
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
