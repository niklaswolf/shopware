<?php declare(strict_types=1);

namespace Shopware\Core\DevOps;

use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 */
#[Package('framework')]
class DevOps extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection'));
        $loader->load('services.xml');

        $environment = $container->getParameter('kernel.environment');
        if ($environment === 'e2e') {
            $loader->load('services_e2e.xml');
        }

        if ($environment === 'dev') {
            $loader->load('services_dev.xml');
        }
    }
}
