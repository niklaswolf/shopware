<?php declare(strict_types=1);

namespace Shopware\Core\System\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Currency\CurrencyCollection;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelType\SalesChannelTypeCollection;

/**
 * @extends EntityCollection<SalesChannelEntity>
 */
#[Package('discovery')]
class SalesChannelCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getLanguageId());
    }

    public function filterByLanguageId(string $id): SalesChannelCollection
    {
        return $this->filter(fn (SalesChannelEntity $salesChannel) => $salesChannel->getLanguageId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getCurrencyIds(): array
    {
        return $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getCurrencyId());
    }

    public function filterByCurrencyId(string $id): SalesChannelCollection
    {
        return $this->filter(fn (SalesChannelEntity $salesChannel) => $salesChannel->getCurrencyId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getPaymentMethodIds(): array
    {
        return $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getPaymentMethodId());
    }

    public function filterByPaymentMethodId(string $id): SalesChannelCollection
    {
        return $this->filter(fn (SalesChannelEntity $salesChannel) => $salesChannel->getPaymentMethodId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getShippingMethodIds(): array
    {
        return $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getShippingMethodId());
    }

    public function filterByShippingMethodId(string $id): SalesChannelCollection
    {
        return $this->filter(fn (SalesChannelEntity $salesChannel) => $salesChannel->getShippingMethodId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getCountryIds(): array
    {
        return $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getCountryId());
    }

    public function filterByCountryId(string $id): SalesChannelCollection
    {
        return $this->filter(fn (SalesChannelEntity $salesChannel) => $salesChannel->getCountryId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getTypeIds(): array
    {
        return $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getTypeId());
    }

    public function filterByTypeId(string $id): SalesChannelCollection
    {
        return $this->filter(fn (SalesChannelEntity $salesChannel) => $salesChannel->getTypeId() === $id);
    }

    public function getLanguages(): LanguageCollection
    {
        return new LanguageCollection(
            $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getLanguage())
        );
    }

    public function getCurrencies(): CurrencyCollection
    {
        return new CurrencyCollection(
            $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getCurrency())
        );
    }

    public function getTypes(): SalesChannelTypeCollection
    {
        return new SalesChannelTypeCollection(
            $this->fmap(fn (SalesChannelEntity $salesChannel) => $salesChannel->getType())
        );
    }

    public function getApiAlias(): string
    {
        return 'sales_channel_collection';
    }

    protected function getExpectedClass(): string
    {
        return SalesChannelEntity::class;
    }
}
