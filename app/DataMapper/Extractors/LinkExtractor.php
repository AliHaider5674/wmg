<?php
namespace App\DataMapper\Extractors;

use Illuminate\Support\Arr;

/**
 * @class LinkExtractor
 */
class LinkExtractor implements ExtractorInterface
{
    private Arr $arr;

    public function __construct(Arr $arr)
    {
        $this->arr = $arr;
    }

    /**
     * @param $path
     * @param $dataSet
     * @return array|\ArrayAccess|mixed
     * @todo support deeper level loop
     */
    public function getData($path, $dataSet)
    {
        $pathBlocks = explode('->', $path);
        $current = array_shift($pathBlocks);
        $currentData = $this->arr::get($dataSet, $current);
        if (count($pathBlocks) === 0) {
            return $currentData;
        }
        if (count($pathBlocks) === 1) {
            return $this->getData(implode('->', $pathBlocks), $dataSet);
        }

        $next = $pathBlocks[0];
        $nextBlocks = explode('.', $next);
        $nextProperty = array_pop($nextBlocks);
        $nextPath = implode('.', $nextBlocks);
        $collection = $this->arr::get($dataSet, $nextPath);
        foreach ($collection as $item) {
            if ($this->arr::get($item, $nextProperty) == $currentData) {
                $this->arr::set($dataSet, $nextPath, $item);
                return $this->getData(implode('->', $pathBlocks), $dataSet);
            }
        }
    }
}
