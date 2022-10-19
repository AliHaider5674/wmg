<?php
/**
 * Convert array or string to UTF 8
 * @param $mixed
 * @return array|false|string|string[]|null
 */
function utf8ize($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}

/**
 * Encode array to json with utf8 parsing
 * @param array $array
 * @return false|string
 */
function utf8_json_encode(array $array)
{
    return json_encode(utf8ize($array));
}
