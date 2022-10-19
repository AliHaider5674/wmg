<?php
namespace Tests\Feature\Mom;

/**
 * A fake client that record requests
 *
 * Class HistoryClient
 * @category WMG
 * @package  Tests\Feature\Mom
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class HistoryClient extends \MomApi\Client
{
    protected $requests = [];

    public function __construct()
    {
    }

    /**
     * @param string $method
     * @param array  $params
     * @param string $to
     * @param false  $isSynchronous
     * @return mixed|void
     * @SuppressWarnings(PHPMD)
     */
    public function publish(string $method, array $params, string $to, $isSynchronous = false)
    {
        $this->requests[] = [
            'method' => $method,
            'params' => $params,
            'to' => $to
        ];
    }

    public function clean()
    {
        $this->requests = [];
    }

    public function getRequests()
    {
        return $this->requests;
    }
}
