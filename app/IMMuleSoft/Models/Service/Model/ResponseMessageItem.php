<?php

namespace App\IMMuleSoft\Models\Service\Model;

use App\Models\Service\Model\Serialize;

/**
 * Class ResponseMessageItem
 * @package App\IMMuleSoft\Models\Service\Model
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ResponseMessageItem extends Serialize
{
    public string $resourceCode;
    public string $statusCode;
    public string $message;
}
