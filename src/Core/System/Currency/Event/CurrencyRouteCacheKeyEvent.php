<?php declare(strict_types=1);

namespace Shopware\Core\System\Currency\Event;

use Shopware\Core\Framework\Adapter\Cache\StoreApiRouteCacheKeyEvent;
use Shopware\Core\Framework\Log\Package;

#[Package('fundamentals@framework')]
class CurrencyRouteCacheKeyEvent extends StoreApiRouteCacheKeyEvent
{
}
