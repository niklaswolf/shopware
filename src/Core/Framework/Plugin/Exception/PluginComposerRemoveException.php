<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Plugin\Exception;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class PluginComposerRemoveException extends ShopwareHttpException
{
    public function __construct(
        string $pluginName,
        string $pluginComposerName,
        string $output
    ) {
        parent::__construct(
            \sprintf('Could not execute "composer remove" for plugin "{{ pluginName }} ({{ pluginComposerName }}). Output:%s{{ output }}', \PHP_EOL),
            [
                'pluginName' => $pluginName,
                'pluginComposerName' => $pluginComposerName,
                'output' => $output,
            ]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__PLUGIN_COMPOSER_REMOVE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
