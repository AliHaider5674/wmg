<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Country regions
 *
 * Class OrderDrop
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.UndefinedVariable)
 */
class CountryRegion extends Model
{
    public static $regionCodeMap;
    public static $regionNameMap;
    public static $hasRegionCountries = ['US'];
    public static function getRegionCode($name, $countryCode = 'US')
    {
        $loadedName = ucwords(strtolower($name));
        $loadedCountryCode = strtoupper($countryCode);
        if (!in_array($loadedCountryCode, self::$hasRegionCountries)) {
            return $name;
        }
        self::initMap();
        if (isset(self::$regionCodeMap[$loadedCountryCode])) {
            if (isset(self::$regionCodeMap[$loadedCountryCode][$loadedName])) {
                return self::$regionCodeMap[$loadedCountryCode][$loadedName];
            }
            if (in_array(strtoupper($name), self::$regionCodeMap[$loadedCountryCode])) {
                return strtoupper($name);
            }
        }
        return $name;
    }

    public static function getRegionName($code, $countryCode = 'US')
    {
        if (!in_array($countryCode, self::$hasRegionCountries)) {
            return $code;
        }
        self::initMap();
        if (isset(self::$regionNameMap[$countryCode])) {
            if (isset(self::$regionNameMap[$countryCode][$code])) {
                return self::$regionNameMap[$countryCode][$code];
            }
            if (in_array($code, self::$regionNameMap[$countryCode])) {
                return $code;
            }
        }
        return '';
    }

    protected static function initMap()
    {
        if (!isset(self::$regionCodeMap) || !isset(self::$regionNameMap)) {
            $regions = self::get();
            self::$regionCodeMap = [];
            self::$regionNameMap = [];
            foreach ($regions as $region) {
                $countryCode = $region->getAttribute('country_code');
                $regionCode = $region->getAttribute('code');
                $regionName = $region->getAttribute('name');
                if (!isset(self::$regionCodeMap[$countryCode])) {
                    self::$regionCodeMap[$countryCode] = [];
                }
                if (!isset(self::$regionNameMap[$countryCode])) {
                    self::$regionNameMap[$countryCode] = [];
                }

                self::$regionCodeMap[$countryCode][$regionName] = $regionCode;
                self::$regionNameMap[$countryCode][$regionCode] = $regionName;
            }
        }
    }
}
