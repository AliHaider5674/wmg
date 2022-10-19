<?php
namespace App\DataMapper\Parsers;

/**
 * @class SyntaxParser
 * @package App\DataMapper
 * A class that parse data mapping
 */
class SyntaxParser
{
    public const REGEX = [
        //|1|
        'static' => '^\|(.*)\|$',
        //order.id ->fulfillment_order.order_id->order.order_id->order.order_number
        'link' => '^([a-zA-Z0-9\.\_]*\-\>){1,}([a-zA-Z0-9\.\_])*$',
        //[]
        'loop' => '^([a-zA-z0-9\.]*)\[\]\.([a-zA-z0-9\.]*)$'
    ];

    public const TYPE_KEY = '__type';

    public function isCallable($path)
    {
        return is_callable($path);
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'is') === 0) {
            $type = substr(strtolower($name), 2);
            return preg_match($this->getRegex($type), $arguments[0]);
        }
        return false;
    }

    public function getRegex($name)
    {
        return '/'. self::REGEX[$name] . '/';
    }
}
