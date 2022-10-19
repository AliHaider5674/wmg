<?php
namespace App\Shopify\Clients\Traits;

use ReflectionClass;

/**
 * @package App\Shopify
 */
trait ResourceLoader
{
    private string $namespace;

    /**
     * @param string $resourceName
     * @param array  $arguments
     * @return mixed|\PHPShopify\ShopifyResource
     * @throws \PHPShopify\Exception\SdkException
     */
    public function __call($resourceName, $arguments)
    {
        if (!isset($this->namespace)) {
            $classReflection = new ReflectionClass(static::class);
            $this->namespace = $classReflection->getNamespaceName();
            $namespaceBlocks = explode('\\', $this->namespace);
            if (array_pop($namespaceBlocks) !== 'Resources') {
                $this->namespace .= '\\Resources';
            }
        }
        if (isset($this->additionalResource) && in_array($resourceName, $this->additionalResource)) {
            $resourceClassName = $this->namespace .'\\'. $resourceName;
            $resourceID = !empty($arguments) ? $arguments[0] : null;
            if (isset($this->resourceUrl)) {
                return app()->makeWith(
                    $resourceClassName,
                    [ 'id' => $resourceID, 'parentResourceUrl' =>$this->resourceUrl]
                );
            }
            return app()->makeWith($resourceClassName, ['id' => $resourceID]);
        }
        return parent::__call($resourceName, $arguments);
    }
}
