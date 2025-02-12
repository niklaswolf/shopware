<?php declare(strict_types=1);

namespace Shopware\Storefront\Pagelet\Header;

use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\Service\NavigationLoaderInterface;
use Shopware\Core\Content\Category\Tree\TreeItem;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\Currency\CurrencyCollection;
use Shopware\Core\System\Currency\SalesChannel\AbstractCurrencyRoute;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\SalesChannel\AbstractLanguageRoute;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelException;
use Shopware\Storefront\Event\RouteRequest\CurrencyRouteRequestEvent;
use Shopware\Storefront\Event\RouteRequest\LanguageRouteRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not use direct or indirect repository calls in a PageletLoader. Always use a store-api route to get or put data.
 */
#[Package('framework')]
class HeaderPageletLoader implements HeaderPageletLoaderInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractCurrencyRoute $currencyRoute,
        private readonly AbstractLanguageRoute $languageRoute,
        private readonly NavigationLoaderInterface $navigationLoader
    ) {
    }

    /**
     * @throws RoutingException
     */
    public function load(Request $request, SalesChannelContext $context): HeaderPagelet
    {
        $salesChannel = $context->getSalesChannel();

        $navigationId = null;
        if (!Feature::isActive('cache_rework')) {
            $navigationId = $request->get('navigationId');
            if ($navigationId !== null) {
                Feature::triggerDeprecationOrThrow(
                    'cache_rework',
                    'The parameter "navigationId" is deprecated and will not be considered anymore with the next major release.'
                );
            }
        }

        $navigationId ??= $salesChannel->getNavigationCategoryId();

        $navigation = $this->navigationLoader->load(
            $navigationId,
            $context,
            $salesChannel->getNavigationCategoryId(),
            $salesChannel->getNavigationCategoryDepth()
        );
        $languages = $this->getLanguages($context, $request);
        $currencies = $this->getCurrencies($request, $context);

        $contextLanguage = $languages->get($context->getLanguageId());
        if (!$contextLanguage) {
            throw SalesChannelException::languageNotFound($context->getLanguageId());
        }

        $page = new HeaderPagelet(
            $navigation,
            $languages,
            $currencies,
            $contextLanguage,
            $context->getCurrency(),
            $this->getServiceMenu($context)
        );

        $this->eventDispatcher->dispatch(new HeaderPageletLoadedEvent($page, $context, $request));

        return $page;
    }

    private function getServiceMenu(SalesChannelContext $context): CategoryCollection
    {
        $serviceId = $context->getSalesChannel()->getServiceCategoryId();

        if ($serviceId === null) {
            return new CategoryCollection();
        }

        $navigation = $this->navigationLoader->load($serviceId, $context, $serviceId, 1);

        return new CategoryCollection(array_map(static fn (TreeItem $treeItem) => $treeItem->getCategory(), $navigation->getTree()));
    }

    private function getLanguages(SalesChannelContext $context, Request $request): LanguageCollection
    {
        $criteria = new Criteria();
        $criteria->setTitle('header::languages');

        $criteria->addFilter(
            new EqualsFilter('language.salesChannelDomains.salesChannelId', $context->getSalesChannelId())
        );
        $criteria->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));

        if (!Feature::isActive('cache_rework')) {
            $criteria->addAssociation('productSearchConfig');
        }

        $event = new LanguageRouteRequestEvent($request, new Request(), $context, $criteria);
        $this->eventDispatcher->dispatch($event);

        return $this->languageRoute->load($event->getStoreApiRequest(), $context, $criteria)->getLanguages();
    }

    private function getCurrencies(Request $request, SalesChannelContext $context): CurrencyCollection
    {
        $criteria = new Criteria();
        $criteria->setTitle('header::currencies');

        $event = new CurrencyRouteRequestEvent($request, new Request(), $context);
        $this->eventDispatcher->dispatch($event);

        return $this->currencyRoute->load($event->getStoreApiRequest(), $context, $criteria)->getCurrencies();
    }
}
