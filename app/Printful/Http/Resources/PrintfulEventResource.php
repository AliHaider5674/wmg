<?php declare(strict_types=1);

namespace App\Printful\Http\Resources;

use App\Printful\Models\PrintfulEvent;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PrintfulEventResource
 * @package App\Printful\Http\Resources
 */
class PrintfulEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param PrintfulEvent $printfulEvent
     * @return array
     */
    public function toArray($printfulEvent): array
    {
        return $this->transformEvent($printfulEvent);
    }

    /**
     * Transform PrintfulEvent
     *
     * @param PrintfulEvent $printfulEvent
     * @return array
     */
    private function transformEvent(PrintfulEvent $printfulEvent): array
    {
        return [
            'id' => $printfulEvent->id,
            'event_type' => $printfulEvent->event_type,
            'event_data' => $printfulEvent->event_data,
            'status' => $printfulEvent->status,
//            'attempts' => $printfulEvent->logs()->count(),
            'created_at' => $printfulEvent->created_at,
            'updated_at' => $printfulEvent->updated_at,
        ];
    }
}
