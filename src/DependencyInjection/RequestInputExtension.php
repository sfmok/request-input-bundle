<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
* @internal
*/
class RequestInputExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        # define a few parameters
        $container->setParameter('request_input.enabled', $config['enabled']);
        $container->setParameter('request_input.formats', $config['formats']);
        $container->setParameter('request_input.skip_validation', $config['skip_validation']);

        $this->loadServicesFiles($container);
    }

    protected function loadServicesFiles(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }
}
