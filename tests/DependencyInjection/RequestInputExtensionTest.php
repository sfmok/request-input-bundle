<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\ArgumentResolver\InputArgumentResolver;
use Sfmok\RequestInput\DependencyInjection\RequestInputExtension;
use Sfmok\RequestInput\EventListener\ExceptionListener;
use Sfmok\RequestInput\EventListener\ReadInputListener;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Metadata\InputMetadataFactory;
use Sfmok\RequestInput\Metadata\InputMetadataFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RequestInputExtensionTest extends TestCase
{
    public const DEFAULT_CONFIG = [
        'request_input' => [
            'enabled' => true,
            'formats' => ['json', 'xml', 'form'],
            'skip_validation' => false
        ]
    ];

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
    }

    public function testLoadConfiguration(): void
    {
        $config = self::DEFAULT_CONFIG;
        (new RequestInputExtension())->load($config, $this->container);

        $services = [
            InputArgumentResolver::class,
            ExceptionListener::class,
            ReadInputListener::class,
            InputFactory::class,
            InputMetadataFactory::class
        ];

        $aliases = [
            InputFactoryInterface::class,
            InputMetadataFactoryInterface::class
        ];

        $this->assertContainerHas($services, $aliases);

        $this->assertServiceHasTags(InputArgumentResolver::class, ['controller.argument_value_resolver']);
        $this->assertServiceHasTags(ExceptionListener::class, ['kernel.event_listener']);
        $this->assertServiceHasTags(ReadInputListener::class, ['kernel.event_listener']);
    }

    public function testLoadConfigurationWithDisabledOption(): void
    {
        (new RequestInputExtension())->load(['request_input' => ['enabled' => false]], $this->container);

        $services = [
            InputArgumentResolver::class,
            ExceptionListener::class,
            ReadInputListener::class,
            InputFactory::class,
            InputMetadataFactory::class
        ];

        foreach ($services as $service) {
            $this->assertFalse($this->container->hasDefinition($service), sprintf('Definition "%s" is found.', $service));
        }
    }

    private function assertContainerHas(array $services, array $aliases = []): void
    {
        foreach ($services as $service) {
            $this->assertTrue($this->container->hasDefinition($service), sprintf('Definition "%s" not found.', $service));
        }

        foreach ($aliases as $alias) {
            $this->assertContainerHasAlias($alias);
        }
    }

    private function assertContainerHasAlias(string $alias): void
    {
        $this->assertTrue($this->container->hasAlias($alias), sprintf('Alias "%s" not found.', $alias));
    }

    private function assertServiceHasTags(string $service, array $tags = []): void
    {
        $serviceTags = $this->container->getDefinition($service)->getTags();

        foreach ($tags as $tag) {
            $this->assertArrayHasKey($tag, $serviceTags, sprintf('Tag "%s" not found on the service "%s".', $tag, $service));
        }
    }
}
