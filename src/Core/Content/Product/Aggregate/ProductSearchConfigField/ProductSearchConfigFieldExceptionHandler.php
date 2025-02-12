<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\Aggregate\ProductSearchConfigField;

use Shopware\Core\Content\Product\Exception\DuplicateProductSearchConfigFieldException;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductSearchConfigFieldExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    /**
     * @param \Exception $e - @deprecated tag:v6.7.0 - in v6.7.0 parameter type will be changed to \Throwable
     */
    public function matchException(\Exception $e): ?\Exception
    {
        if (preg_match('/SQLSTATE\[23000\]:.*1062 Duplicate.*uniq.search_config_field.field__config_id\'/', $e->getMessage())) {
            $field = [];
            preg_match('/Duplicate entry \'(.*)\' for key/', $e->getMessage(), $field);
            $fieldNameMatch = $field[1] ?? '';
            $field = substr($fieldNameMatch, 0, (int) strpos($fieldNameMatch, '-'));

            return new DuplicateProductSearchConfigFieldException($field, $e);
        }

        return null;
    }
}
