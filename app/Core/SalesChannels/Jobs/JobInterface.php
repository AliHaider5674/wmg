<?php
namespace App\Core\SalesChannels\Jobs;

interface JobInterface
{
    public function config(Array $config);
    public function run();
    public function getName();
}
