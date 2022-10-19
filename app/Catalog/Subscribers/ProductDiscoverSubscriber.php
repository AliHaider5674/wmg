<?php
namespace App\Catalog\Subscribers;

use App\Catalog\Repositories\ProductRepository;

/**
 * A subscriber that monitor orders coming in
 *
 * Class ProductDiscoverSubscriber
 * @category WMG
 * @package  App\Catalog
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ProductDiscoverSubscriber
{
    const CATALOG_PRODUCT_DISCOVER = 'catalog.product.discover';
    private ProductRepository $productRepository;
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param $productData
     */
    public function handle($productData)
    {
        $sku = $productData['sku'] ?? null;
        if (empty($sku)) {
            return;
        }
        $product = $this->productRepository->loadBySku($productData['sku']);
        $data = $productData;
        if (!$product) {
            $this->productRepository->create($data);
            return;
        } elseif ($product->preorder && empty($data['preorder'])) {
            //@todo move this to preorder module
            //adding this to avoid users place orders from Shopify admin
            //where preorder information won't be sent.
            return;
        }
        $this->productRepository->update($product, $data);
    }

    public function subscribe($events)
    {
        $events->listen(
            self::CATALOG_PRODUCT_DISCOVER,
            'App\Catalog\Subscribers\ProductDiscoverSubscriber@handle'
        );
    }
}
