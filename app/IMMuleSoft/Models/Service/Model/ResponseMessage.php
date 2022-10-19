<?php

namespace App\IMMuleSoft\Models\Service\Model;

use App\IMMuleSoft\Constants\ConfigConstant;
use App\IMMuleSoft\Constants\ResourceConstant;
use App\Models\Service\Model\Serialize;

/**
 * Class ResponseMessage
 * @package App\IMMuleSoft\Models\Service\Model
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ResponseMessage extends Serialize
{
    public string $statusCode = ResourceConstant::RESPONSE_STATUS_CODE_SUCCESS;
    public string $message;
    public string $resourceType;
    public array $responses = [];

    /**
     * newResponseMessageItem
     */
    public function newResponseMessageItem($item)
    {
        $messageItem = new ResponseMessageItem();
        $messageItem->resourceCode = $item['resourceCode'];
        $messageItem->statusCode = $item['statusCode'];
        $messageItem->message = $item['message'];

        $this->responses[] = $messageItem;
    }
}
