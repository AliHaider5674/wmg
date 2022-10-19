<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order item model that reference to
 * order_items table
 *
 * Class OrderItem
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CustomAttribute extends Model
{
    private $customAttributes;
    public function setCustomAttributes(array $attributes = null)
    {
        $this->attributeToAssociate($attributes);
        $this->setAttribute('custom_attributes', utf8_json_encode($this->customAttributes));
    }

    /**
     * Get custom attribute
     * @param $attributeName
     * @return mixed|null
     */
    public function getCustomAttribute($attributeName)
    {
        $customAttributes = $this->getCustomAttributes();
        return isset($customAttributes[$attributeName])
            ? $customAttributes[$attributeName]
            : null;
    }

    public function getCustomAttributes()
    {
        if (!isset($this->customAttributes)) {
            $attribute = $this->getAttribute('custom_attributes');
            $this->customAttributes = [];
            if ($attribute) {
                $this->customAttributes = json_decode($attribute, true);
            }
        }
        return $this->customAttributes;
    }

    private function attributeToAssociate($attributes)
    {
        $this->customAttributes = [];
        if (is_array($attributes)) {
            foreach ($attributes as $attribute) {
                $key = data_get($attribute, 'name');
                if ($key === null) {
                    continue;
                }
                $this->customAttributes[$attribute['name']] = data_get($attribute, 'value');
            }
        }
        return $this;
    }

    /**
     * addCustomAttribute
     * @param $attributes
     * @return bool
     */
    public function addCustomAttribute($attributes) : bool
    {
        if (!is_array($attributes)) {
            return false;
        }

        $this->getCustomAttributes();
        foreach ($attributes as $key => $value) {
            $this->customAttributes[$key] = $value;
        }
        $this->setAttribute('custom_attributes', utf8_json_encode($this->customAttributes));

        return true;
    }
}
