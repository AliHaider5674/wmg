<?php
namespace App\DataMapper\Extractors;

/**
 * @interface ExtractorInterface
 */
interface ExtractorInterface
{
    public function getData($path, $dataSet);
}
