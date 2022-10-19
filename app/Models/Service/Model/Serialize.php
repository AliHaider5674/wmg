<?php

namespace App\Models\Service\Model;

use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Make model is serializable
 *
 * Class Serialize
 * @category WMG
 * @package  App\Models\Service\Model
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Serialize
{
    private $hiddenDataOnly = [];
    /**
     * Serialize properties to array
     * @param bool  $isCamelCase
     * @param array $data
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @return array
     */
    public function toArray($isCamelCase = true, $data = null)
    {
        $properties = $data;
        if ($data === null) {
            $properties = get_object_vars($this);
            unset($properties['hiddenDataOnly']);
        }

        foreach ($properties as $key => $value) {
            if (!$isCamelCase) {
                $key = $this->toUnderscore($key, $properties);
            }
            if ($value instanceof Serialize) {
                $properties[$key] = $value->toArray($isCamelCase);
            } elseif (is_array($value)) {
                $properties[$key] = $this->toArray($isCamelCase, $value);
            }
        }
        return $properties;
    }

    /**
     * Fill data
     * @param array $data
     * @param bool  $isDataCamelCase
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @return $this
     */
    public function fill(array $data, $isDataCamelCase = true)
    {
        $reflectionClass = new ReflectionClass($this);
        foreach ($data as $key => $value) {
            if (!$isDataCamelCase) {
                $key = Str::camel($key);
            }
            if ($reflectionClass->hasProperty($key)) {
                $this->assignValueToProperty($key, $value);
            }
        }
        return $this;
    }

    public function setHiddenData($key, $value)
    {
        $this->hiddenDataOnly[$key] = $value;
        return $this;
    }

    public function getHiddenData($key)
    {
        return $this->hiddenDataOnly[$key] ?? null;
    }

    /**
     * Camel case to underscore
     *
     * @param $key
     * @param $array
     *
     * @return string
     */
    protected function toUnderscore($key, &$array)
    {
        $newKey = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', "_$1", $key));
        if ($newKey !== $key) {
            $data = $array[$key];
            unset($array[$key]);
            $array[$newKey] = $data;
        }
        return $newKey;
    }


    private function assignValueToProperty(String $property, $value) : void
    {
        $reflectionClass = new ReflectionClass($this);
        if (!$reflectionClass->hasProperty($property)) {
            return;
        }
        $type = $reflectionClass->getProperty($property)->getType();
        $typeName = $type ? $type->getName() : 'string';
        switch ($typeName) {
            case 'int':
            case 'string':
            case 'array':
                $this->{$property} = $value;
                break;
            default:
                $object = new $typeName();
                if ($object instanceof Serialize) {
                    $object->fill($value ?? []);
                }
                $this->{$property} = $object;
                break;
        }
    }
}
