<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\CrossSellingStruct;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\AbstractProductCrossSellingRoute;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Request;

#[Package('buyers-experience')]
class CrossSellingCmsElementResolver extends AbstractProductDetailCmsElementResolver
{
    final public const TYPE = 'cross-selling';

    /**
     * @internal
     */
    public function __construct(private readonly AbstractProductCrossSellingRoute $crossSellingLoader)
    {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $context = $resolverContext->getSalesChannelContext();
        $struct = new CrossSellingStruct();
        $slot->setData($struct);

        $productConfig = $config->get('product');

        if ($productConfig === null || $productConfig->getValue() === null) {
            return;
        }

        $product = null;

        if ($productConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $product = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());
        }

        if ($productConfig->isStatic()) {
            $product = $this->getSlotProduct($slot, $result, $productConfig->getStringValue());
        }

        if (!$product instanceof SalesChannelProductEntity) {
            return;
        }

        // ensure to use the parent id, so that the cross-sells cache-tag on product page is build with the correct product-id
        // cross-sells are managed through parent products, so also the invalidation contains the parent id.
        $productId = $product->getParentId() ?? $product->getId();
        $crossSellings = $this->crossSellingLoader->load($productId, new Request(), $context, new Criteria())->getResult();

        if ($crossSellings->count()) {
            $struct->setCrossSellings($crossSellings);
        }
    }
}
