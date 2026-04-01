<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @internal
 */
#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();
        $config = new Processor()->processConfiguration($configuration, []);

        $this->assertInstanceOf(ConfigurationInterface::class, $configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertEquals([
            'enabled' => true,
            'validation' => [
                'skip' => false,
                'status_code' => 400,
            ],
            'serialization' => [
                'context' => [],
            ],
        ], $config);
    }
}
