<?php
namespace App\Core\Models\RawData;

use App\Models\Service\Model\Serialize;

/**
 * Raw order address model for Fulfillment IO to use
 *
 * Class OrderAddress
 * @category WMG
 * @package  App\Core\Models\RawData
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD)
 */
class OrderAddress extends Serialize
{
    public $id;
    public $firstName;
    public $lastName;
    public $customerName;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $stateCode;
    public $zip;
    public $countryCode;
    public $phone;
    public $email;
    public $latitude;
    public $longitude;
    public $customerAddressType;

    /**
     * @var array
     */
    public $customAttributes = [];

    public $taxId;
    public $createdAt;
    public $updatedAt;
}
