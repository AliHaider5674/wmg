<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Filesystem\Filesystem;
use WMGCore\Services\ConfigService;

/**
 * Base Test Case
 *
 * Class TestCase
 * @category WMG
 * @package  Tests
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * @var Generator $faker
     */
    protected $faker;

    /**
     * @var Helper $helper
     */
    protected $helper;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->helper = new Helper($this);

        //Clear
        $this->fileSystem = new Filesystem();

        if ($this->fileSystem->exists(storage_path('test'))) {
            $this->fileSystem->remove(storage_path('test'));
        }

        app()->make(ConfigService::class)->load();
    }

    /**
     * Tear down tests
     */
    public function tearDown(): void
    {
        //Clear files
        if ($this->fileSystem) {
            if ($this->fileSystem->exists(storage_path('test'))) {
                $this->fileSystem->remove(storage_path('test'));
            }
        }
        parent::tearDown();
    }

    /**
     * @return Generator
     */
    public function getFaker(): Generator
    {
        return $this->faker;
    }

    /**
     * @return Helper
     */
    public function getHelper(): Helper
    {
        return $this->helper;
    }

    /**
     * @return Application
     */
    public function getApp(): Application
    {
        return $this->app;
    }
}
