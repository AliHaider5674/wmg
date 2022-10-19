<?php declare(strict_types=1);

namespace App\Printful\Structures;

use Printful\Structures\Order\OrderItemCreationParameters as BaseOrderItemCreationParameters;

/**
 * Class OrderItemCreationParameters
 * @package App\Printful\Structures
 */
class OrderItemCreationParameters extends BaseOrderItemCreationParameters
{
    /**
     * @var string
     */
    public $externalVariantId;

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @param string $externalVariantId
     * @return $this
     */
    public function setExternalVariantId(string $externalVariantId): self
    {
        $this->externalVariantId = $externalVariantId;

        return $this;
    }

    /**
     * @param int $externalSyncVariantId
     * @return $this
     */
    public function setExternalSyncVariantId(int $externalSyncVariantId): self
    {
        $this->variantId = $externalSyncVariantId;

        return $this;
    }

    /**
     * @param string $retailPrice
     * @return $this
     */
    public function setRetailPrice(string $retailPrice): self
    {
        $this->retailPrice = $retailPrice;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $return = array_merge(parent::toArray(), [
            'external_variant_id' => $this->externalVariantId
        ]);

        unset($return['files']);

        return $return;
    }
}
