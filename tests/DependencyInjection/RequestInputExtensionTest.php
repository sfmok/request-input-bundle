<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\ArgumentResolver\InputArgumentResolver;
use Sfmok\RequestInput\DependencyInjection\RequestInputExtension;
use Sfmok\RequestInput\EventListener\ExceptionListener;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RequestInputExtensionTest extends TestCase
{
    public const DEFAULT_CONFIG = [
        'request_input' => [
            'enabled' => true,
            'formats' => ['json', 'xml', 'form'],
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
            InputFactory::class,
        ];

        $aliases = [
            InputFactoryInterface::class
        ];

        $parameters = [
            'request_input.enabled' => $config['request_input']['enabled'],
            'request_input.formats' => $config['request_input']['formats'],
        ];

        $this->assertContainerHas($services, $aliases, $parameters);

        $this->assertServiceHasTags(InputArgumentResolver::class, ['controller.argument_value_resolver']);
        $this->assertServiceHasTags(ExceptionListener::class, ['kernel.event_listener']);
    }

    private function assertContainerHas(array $services, array $aliases = [], array $parameters = []): void
    {
        foreach ($services as $service) {
            $this->assertTrue($this->container->hasDefinition($service), sprintf('Definition "%s" not found.', $service));
        }

        foreach ($aliases as $alias) {
            $this->assertContainerHasAlias($alias);
        }

        foreach ($parameters as $parameterKey => $parameterValue) {
            $this->assertContainerHasParameter($parameterKey, $parameterValue);
        }
    }

    private function assertContainerHasAlias(string $alias): void
    {
        $this->assertTrue($this->container->hasAlias($alias), sprintf('Alias "%s" not found.', $alias));
    }

    private function assertContainerHasParameter(string $parameterKey, $parameterValue): void
    {
        $this->assertTrue($this->container->hasParameter($parameterKey), sprintf('Parameter "%s" not found.', $parameterKey));
        $this->assertSame($this->container->getParameter($parameterKey), $parameterValue);
    }

    private function assertServiceHasTags(string $service, array $tags = []): void
    {
        $serviceTags = $this->container->getDefinition($service)->getTags();

        foreach ($tags as $tag) {
            $this->assertArrayHasKey($tag, $serviceTags, sprintf('Tag "%s" not found on the service "%s".', $tag, $service));
        }
    }
}
