<?php declare(strict_types=1);

namespace Shopware\Tests\Integration\Core\Framework\DataAbstractionLayer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionTranslation\PromotionTranslationDefinition;
use Shopware\Core\Checkout\Promotion\PromotionDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsPageTranslation\CmsPageTranslationDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlotTranslation\CmsSlotTranslationDefinition;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupTranslation\PropertyGroupTranslationDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldVisibility;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateTranslationDefinition;
use Shopware\Core\System\StateMachine\StateMachineDefinition;
use Shopware\Core\System\StateMachine\StateMachineTranslationDefinition;

/**
 * @internal
 */
class EntityDefinitionTest extends TestCase
{
    use KernelTestBehaviour;

    public function testEntityDefinitionCompilation(): void
    {
        $definition = static::getContainer()->get(ProductDefinition::class);

        static::assertContainsOnlyInstancesOf(Field::class, $definition->getFields());
        $productManufacturerVersionIdField = $definition->getFields()->get('productManufacturerVersionId');
        static::assertInstanceOf(ReferenceVersionField::class, $productManufacturerVersionIdField);
        static::assertSame('product_manufacturer_version_id', $productManufacturerVersionIdField->getStorageName());
        static::assertInstanceOf(ProductManufacturerDefinition::class, $productManufacturerVersionIdField->getVersionReferenceDefinition());
        static::assertSame(static::getContainer()->get(ProductManufacturerDefinition::class), $productManufacturerVersionIdField->getVersionReferenceDefinition());
    }

    public function testTranslationCompilation(): void
    {
        $definition = static::getContainer()->get(ProductTranslationDefinition::class);

        static::assertContainsOnlyInstancesOf(Field::class, $definition->getFields());
        $languageIdField = $definition->getFields()->get('languageId');
        static::assertInstanceOf(FkField::class, $languageIdField);
        static::assertSame('language_id', $languageIdField->getStorageName());
    }

    #[DataProvider('provideTranslatedDefinitions')]
    public function testTranslationsOnDefinitions(string $baseDefinitionClass, string $translationDefinitionClass): void
    {
        $baseDefinition = static::getContainer()->get($baseDefinitionClass);
        $translationDefinition = static::getContainer()->get($translationDefinitionClass);

        static::assertInstanceOf(EntityDefinition::class, $baseDefinition);
        static::assertInstanceOf(EntityTranslationDefinition::class, $translationDefinition);
        static::assertSame($translationDefinition, $baseDefinition->getTranslationDefinition());
        static::assertInstanceOf(JsonField::class, $baseDefinition->getFields()->get('translated'));
        static::assertSame($baseDefinition->getClass(), $translationDefinition->getParentDefinition()->getClass());
        static::assertSame($baseDefinition, $translationDefinition->getParentDefinition());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function provideTranslatedDefinitions(): array
    {
        return [
            [CmsPageDefinition::class, CmsPageTranslationDefinition::class],
            [CmsSlotDefinition::class, CmsSlotTranslationDefinition::class],
            [PropertyGroupDefinition::class, PropertyGroupTranslationDefinition::class],
            [StateMachineDefinition::class, StateMachineTranslationDefinition::class],
            [StateMachineStateDefinition::class, StateMachineStateTranslationDefinition::class],
            [ProductDefinition::class, ProductTranslationDefinition::class],
            [PromotionDefinition::class, PromotionTranslationDefinition::class],
        ];
    }

    public function testGetFieldVisibility(): void
    {
        $definition = static::getContainer()->get(CustomerDefinition::class);

        $internalFields = [
            'password',
            'newsletterSalesChannelIds',
            'legacyPassword',
            'legacyEncoder',
        ];

        foreach ($internalFields as $field) {
            static::assertTrue($definition->getFieldVisibility()->isVisible($field));
            FieldVisibility::$isInTwigRenderingContext = true;
            static::assertFalse($definition->getFieldVisibility()->isVisible($field));
            FieldVisibility::$isInTwigRenderingContext = false;
        }
    }
}
