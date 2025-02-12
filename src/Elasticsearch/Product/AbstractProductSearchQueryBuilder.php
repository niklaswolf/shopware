<?php declare(strict_types=1);

namespace Shopware\Elasticsearch\Product;

use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractProductSearchQueryBuilder
{
    abstract public function getDecorated(): AbstractProductSearchQueryBuilder;

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - will return BuilderInterface in the future
     */
    abstract public function build(Criteria $criteria, Context $context): BoolQuery;
}
