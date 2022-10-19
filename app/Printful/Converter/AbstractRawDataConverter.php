<?php declare(strict_types=1);

namespace App\Printful\Converter;

use App\Models\Service\Model\Serialize;

/**
 * Class AbstractRawDataConverter
 * @package App\Printful\Converter
 */
class AbstractRawDataConverter
{
    /**
     * @param Serialize $originalModel
     * @param           $newModel
     * @param array     $map
     * @return mixed
     */
    protected function mapParameters(
        Serialize $originalModel,
        $newModel,
        array $map
    ) {
        foreach ($map as $originalModelAttributeKey => $newModelAttributeKey) {
            if (!isset($originalModel->$originalModelAttributeKey)) {
                continue;
            }

            $newModel->$newModelAttributeKey = $originalModel->$originalModelAttributeKey;
        }

        return $newModel;
    }
}
