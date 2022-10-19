<?php
namespace App\Shopify\Structures\Shopify;

use App\Models\Service\Model\Serialize;

/**
 * @class Address
 * @package App\Models
 * Shopify fulfillment Order
 */
class Address extends Serialize
{
    public ?Int $id;
    public ?String $address1;
    public ?String $address2;
    public ?String $city;
    public ?String $company;
    public ?String $email;
    public ?String $firstName;
    public ?String $lastName;
    public ?String $phone;
    public ?String $province;
    public ?String $zip;
}
