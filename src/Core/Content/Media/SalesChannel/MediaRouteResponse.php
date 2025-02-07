<?php declare(strict_types=1);

namespace Shopware\Core\Content\Media\SalesChannel;

use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('discovery')]
class MediaRouteResponse extends StoreApiResponse
{
    /**
     * @var MediaCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $object;

    public function __construct(MediaCollection $mediaCollection)
    {
        parent::__construct($mediaCollection);
    }

    public function getMediaCollection(): MediaCollection
    {
        return $this->object;
    }
}
