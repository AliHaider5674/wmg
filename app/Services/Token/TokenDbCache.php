<?php
namespace App\Services\Token;

use App\Models\Service;
use App\Core\Models\ServiceData;

/**
 * Token cache that save token into data
 *
 * Class TokenDbCache
 * @category WMG
 * @package  App\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class TokenDbCache
{
    private $cache = [];
    /**
     * Description here
     *
     * @return mixed
     */
    public function save($token, Service $service)
    {
        $data = $service->datas()
            ->where('key', '=', 'token')
            ->first();

        if (!$data) {
            $data = new ServiceData();
        }

        $data->fill([
            'parent_id' => $service->id,
            'key' => 'token',
            'value' => $token
        ]);
        $data->save();
        $this->cache[$service->id] = $token;
        return $data;
    }

    /**
     * Description here
     *
     * @return mixed|null
     */
    public function get(Service $service)
    {
        if (!isset($this->cache[$service->id])) {
            $token = $service->datas()
                ->where('key', '=', 'token')
                ->first();
            $this->cache[$service->id] = $token ? $token->value : null;
        }
        return $this->cache[$service->id];
    }
}
