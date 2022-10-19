<?php

namespace App\IMMuleSoft\Models\Service\ModelBuilder;

use App\IMMuleSoft\Models\Service\Model\ResponseMessage;

/**
 * Class ResponseMessageBuilder
 * @package App\IMMuleSoft\Models\Service\ModelBuilder
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ResponseMessageBuilder
{
    public function build(
        $statusCode,
        $message,
        $resourceType,
        $items
    ): ResponseMessage {
        $responseMessage = new ResponseMessage();

        $responseMessage->statusCode = $statusCode;
        $responseMessage->message = $message;
        $responseMessage->resourceType = $resourceType;

        foreach ($items as $item) {
            $responseMessage->newResponseMessageItem($item);
        }

        return $responseMessage;
    }
}
