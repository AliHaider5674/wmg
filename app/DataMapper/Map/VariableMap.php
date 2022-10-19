<?php
namespace App\DataMapper\Map;

/**
 * @class VariableMap
 * @package App\DataMapper
 * A map that allow users to set on the fly
 */
class VariableMap implements MapInterface
{
    private $map;
    public function __construct($map = [])
    {
        $this->map = $map;
    }
    public function getMap() : array
    {
        return $this->map;
    }

    public function setMap(array $map)
    {
        $this->map = $map;
    }
}
