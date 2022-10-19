<?php
namespace App\DataMapper\Extractors;

/**
 * @class CallableExtractor
 */
class CallableExtractor implements ExtractorInterface
{
    /**
     * @param $path
     * @param $dataSet
     * @return false|mixed
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function getData($path, $dataSet)
    {
        return call_user_func($path, $dataSet);
    }
}
