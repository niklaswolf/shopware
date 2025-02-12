<?php declare(strict_types=1);

namespace Shopware\Core\Content\Seo;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('inventory')]
interface SeoUrlPlaceholderHandlerInterface
{
    /**
     * @param string $name
     * @param array<mixed> $parameters
     */
    public function generate($name, array $parameters = []): string;

    public function replace(string $content, string $host, SalesChannelContext $context): string;
}
