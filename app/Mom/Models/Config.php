<?php
namespace App\Mom\Models;

use Symfony\Component\Yaml\Yaml;

/**
 * A model that load MOM event configurations
 *
 * Class Config
 * @category WMG
 * @package  App\Mom
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Config
{
    protected $config;
    protected $events;
    public function load($file)
    {
        $this->config = Yaml::parseFile($file);
    }

    public function getEvents()
    {
        return $this->config['events'];
    }
}
