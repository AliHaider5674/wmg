<?php
namespace App\Preorder\Service;

use App\Catalog\Repositories\ProductRepository;

/**
 * @class Product service
 */
class ProductService
{
    private $productPreorderCache;
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productPreorderCache = [];
        $this->productRepository = $productRepository;
    }

    public function getPreorderBySku($sku)
    {
        if (!isset($this->productPreorderCache[$sku])) {
            $product = $this->productRepository->loadBySku($sku);
            $this->productPreorderCache[$sku] = $product ? $product->preorder : null;
        }
        return $this->productPreorderCache[$sku];
    }
}
