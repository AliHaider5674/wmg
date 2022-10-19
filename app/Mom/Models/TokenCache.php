<?php
namespace App\Mom\Models;

use Carbon\Carbon;
use MomApi\Auth\TokenCacheInterface;
use WMGCore\Services\AppDataService;

/**
 * Token Cache that save mom token
 *
 * Class TokenCache
 * @category WMG
 * @package  App\Mom
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class TokenCache implements TokenCacheInterface
{
    private $appDataService;
    public function __construct(AppDataService $appDataService)
    {
        $this->appDataService = $appDataService;
    }

    const APP_DATA_KEY = 'mom.token';
    /**
     * @return string
     */
    public function get(): string
    {
        try {
            $data = $this->appDataService->getJson(self::APP_DATA_KEY);
            if (!$data) {
                return '';
            }
            $token = $data['token'];
            $current = new Carbon(null, 'UTC');
            $expiration = new Carbon($data['expires'], 'UTC');
            $isExpired = $current->diffInSeconds($expiration, false) < 0 ? true : false;
            if ($isExpired) {
                return '';
            }
            return $token;
        } catch (\Exception $exception) {
            return '';
        }
    }
    /**
     * @param string $token
     * @param int $expires
     */
    public function save(string $token, int $expires): void
    {
        $expiryDate = Carbon::createFromTimestamp($expires, 'UTC')->toDateTimeString();
        $this->appDataService->update(self::APP_DATA_KEY, [
            'token' => $token,
            'expires' => $expiryDate
        ]);
    }
}
