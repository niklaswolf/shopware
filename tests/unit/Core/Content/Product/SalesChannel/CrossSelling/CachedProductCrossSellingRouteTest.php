<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Content\Product\SalesChannel\CrossSelling;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Events\CrossSellingRouteCacheKeyEvent;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\AbstractProductCrossSellingRoute;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CachedProductCrossSellingRoute;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElementCollection;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\ProductCrossSellingRouteResponse;
use Shopware\Core\Framework\Adapter\Cache\AbstractCacheTracer;
use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\Test\Generator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @deprecated tag:v6.7.0 - Remove full class
 *
 * @internal
 */
#[CoversClass(CachedProductCrossSellingRoute::class)]
class CachedProductCrossSellingRouteTest extends TestCase
{
    private MockObject&AbstractProductCrossSellingRoute $decorated;

    private MockObject&CacheInterface $cache;

    private EventDispatcher $eventDispatcher;

    private CachedProductCrossSellingRoute $cachedRoute;

    private SalesChannelContext $context;

    private ProductCrossSellingRouteResponse $response;

    protected function setUp(): void
    {
        Feature::skipTestIfActive('cache_rework', $this);
        $this->decorated = $this->createMock(AbstractProductCrossSellingRoute::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->eventDispatcher = new EventDispatcher();
        $salesChannel = new SalesChannelEntity();
        $salesChannel->setId(Uuid::randomHex());
        $this->context = Generator::generateSalesChannelContext(
            baseContext: new Context(new SalesChannelApiSource(Uuid::randomHex())),
            salesChannel: $salesChannel
        );
        $this->response = new ProductCrossSellingRouteResponse(
            new CrossSellingElementCollection()
        );

        $this->cachedRoute = new CachedProductCrossSellingRoute(
            $this->decorated,
            $this->cache,
            $this->createMock(EntityCacheKeyGenerator::class),
            $this->createMock(AbstractCacheTracer::class),
            $this->eventDispatcher,
            []
        );
    }

    public function testLoadWithDisabledCacheWillCallDecoratedRoute(): void
    {
        $this->decorated
            ->expects(static::once())
            ->method('load')
            ->willReturn($this->response);
        $this->cache
            ->expects(static::never())
            ->method('get');
        $this->eventDispatcher->addListener(
            CrossSellingRouteCacheKeyEvent::class,
            fn (CrossSellingRouteCacheKeyEvent $event) => $event->disableCaching()
        );

        $this->cachedRoute->load('', new Request(), $this->context, new Criteria());
    }

    public function testLoadWithEnabledCacheWillReturnDataFromCache(): void
    {
        $this->decorated
            ->expects(static::never())
            ->method('load');
        $this->cache
            ->expects(static::once())
            ->method('get')
            ->willReturn(CacheValueCompressor::compress($this->response));
        $this->eventDispatcher->addListener(
            CrossSellingRouteCacheKeyEvent::class,
            fn (CrossSellingRouteCacheKeyEvent $event) => $event
        );

        $this->cachedRoute->load('', new Request(), $this->context, new Criteria());
    }
}
