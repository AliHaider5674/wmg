<?php

namespace App\Printful\Service;

use App\Printful\Configurations\PrintfulConfig;
use Printful\Exceptions\PrintfulApiException;
use Printful\Exceptions\PrintfulException;
use Printful\PrintfulApiClient;
use Illuminate\Support\Facades\Cache;

/**
 * Class PrintfulCountryService
 * @package App\Printful\Service
 *
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class PrintfulCountryService
{
    const URI = 'countries';
    const COUNTRIES_CACHE_KEY = 'printful_countries';
    const COUNTRIES_ID_CACHE_KEY = 'printful_countries_id';

    /**
     * @var PrintfulApiClient
     */
    private $client;

    /**
     * @var array
     */
    private $countryCodes;

    /**
     * @var array
     */
    private $countries;
    /**
     * @var PrintfulConfig
     */
    private $printfulConfig;

    /**
     * @var array|null
     */
    private $customCountryStateMap;

    /**
     * PrintfulCountryService constructor.
     * @param PrintfulApiClient $printfulApiClient
     * @param PrintfulConfig $printfulConfig
     */
    public function __construct(
        PrintfulApiClient $printfulApiClient,
        PrintfulConfig $printfulConfig
    ) {
        $this->client = $printfulApiClient;
        $this->printfulConfig = $printfulConfig;

        $this->customCountryStateMap =  $this->printfulConfig->getCustomCountryStateMap();

        if (Cache::has(self::COUNTRIES_CACHE_KEY)) {
            $this->countries = Cache::get(self::COUNTRIES_CACHE_KEY);
        }

        if (Cache::has(self::COUNTRIES_ID_CACHE_KEY)) {
            $this->countryCodes = Cache::get(self::COUNTRIES_ID_CACHE_KEY);
        }
    }

    /**
     * getCountries
     * @return array
     * @throws PrintfulApiException
     * @throws PrintfulException
     */
    public function getCountries(): array
    {
        if ($this->countries) {
            return $this->countries;
        }

        return $this->countries = $this->getCountriesFromApi();
    }


    /**
     * applyCustomStateMapping
     * @param array $countries
     * @return array
     */
    protected function applyCustomStateMapping(array $countries) : array
    {
        if (empty($this->customCountryStateMap) || !is_array($countries)) {
            return $countries;
        }


        $mappedStates = array_merge_recursive($countries, $this->customCountryStateMap);

        if (is_null($mappedStates)) {
            return $countries;
        }

        return $mappedStates;
    }

    /**
     * getCountriesFromApi
     * @return array
     * @throws PrintfulApiException
     * @throws PrintfulException
     */
    protected function getCountriesFromApi(): array
    {
        $countries = array();

        //request countries data from Printful APIx
        $countriesFromApi = $this->client->get(self::URI);

        //Only require countries where Printful support country state codes
        if (!empty($countriesFromApi)) {
            foreach ($countriesFromApi as $country) {
                if (isset($country['states']) && !empty($country['states'])
                    && isset($country['code'])
                ) {
                    $countries[$country['code']]= $country;
                }
            }
        }

        $countries = $this->applyCustomStateMapping($countries);

        Cache::put(self::COUNTRIES_CACHE_KEY, $countries);
        return $countries;
    }

    /**
     * isStateCodeRequired
     * @param String $countryCode
     * @return bool
     * @throws PrintfulApiException
     * @throws PrintfulException
     */
    public function isStateCodeRequired(String $countryCode) : bool
    {
        return (in_array(strtoupper($countryCode), $this->getApplicableCountryCodes()));
    }

    /**
     * getApplicableCountryCodes
     * @return array
     * @throws PrintfulApiException
     * @throws PrintfulException
     */
    public function getApplicableCountryCodes() : array
    {
        if (!empty($this->countryCodes)) {
            return $this->countryCodes;
        }

        $countries = $this->getCountries();
        $this->countryCodes = array_keys($countries);

        Cache::put(self::COUNTRIES_ID_CACHE_KEY, $this->countryCodes);
        return $this->countryCodes;
    }

    /**
     * getStateByCountry
     * @param String $countryCode
     * @return array
     * @throws PrintfulApiException
     * @throws PrintfulException
     */
    public function getStateByCountry(String $countryCode) : array
    {
        foreach ($this->getCountries() as $code => $country) {
            if ($code === $countryCode) {
                return $country['states'];
            }
        }

        return array();
    }
}
