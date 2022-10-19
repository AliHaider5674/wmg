<?php
namespace App\Models\Validator;

/**
 * Regex rule validator
 *
 * Class RegexRuleValidator
 * @category WMG
 * @package  App\Models\Validator
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class RegexRuleValidator implements ValidatorInterface
{
    public function isPassed($arg, $data)
    {
        $result = true;
        if (is_array($arg)) {
            foreach ($arg as $key => $value) {
                $currentValue = data_get($data, $key);

                if ($currentValue === null) {
                    continue;
                }
                if (!preg_match("/$value/i", $currentValue)) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }
}
