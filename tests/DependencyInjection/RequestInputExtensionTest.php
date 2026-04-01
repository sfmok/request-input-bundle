<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\DependencyInjection\RequestInputExtension;
use Sfmok\RequestInput\EventListener\ExceptionListener;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Metadata\InputMetadataResolver;
use Sfmok\RequestInput\Metadata\InputMetadataResolverInterface;
use Sfmok\RequestInput\ValueResolver\InputValueResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
#[CoversClass(RequestInputExtension::class)]
class RequestInputExtensionTest extends TestCase
{
    public const DEFAULT_CONFIG = [
        'request_input' => [
            'enabled' => true,
            'validation' => [
                'skip' => false,
                'status_code' => 400,
            ],
            'serialization' => [
                'context' => [],
            ],
        ],
    ];

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
    }

    public function testLoadConfiguration(): void
    {
        $config = self::DEFAULT_CONFIG;
        new RequestInputExtension()->load($config, $this->container);

        $services = [
            InputValueResolver::class,
            ExceptionListener::class,
            InputFactory::class,
            InputMetadataResolver::class,
        ];

        $aliases = [
            InputFactoryInterface::class,
            InputMetadataResolverInterface::class,
        ];

        $this->assertContainerHas($services, $aliases);

        $this->assertServiceHasTags(InputValueResolver::class, ['controller.argument_value_resolver']);
        $this->assertServiceHasTags(ExceptionListener::class, ['kernel.event_listener']);
    }

    public function testLoadConfigurationWithDisabledOption(): void
    {
        new RequestInputExtension()->load(['request_input' => ['enabled' => false]], $this->container);

        $services = [
            InputValueResolver::class,
            ExceptionListener::class,
            InputFactory::class,
            InputMetadataResolver::class,
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
