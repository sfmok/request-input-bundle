<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();
        $config = (new Processor())->processConfiguration($configuration, []);

        $this->assertInstanceOf(ConfigurationInterface::class, $configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertEquals([
            'enabled' => true,
            'formats' => ['json', 'xml', 'form'],
            'skip_validation' => false
        ], $config);

    }

    #[DataProvider('invalidFormatsProvider')]
    public function testInvalidFormatsConfig(array $formats): void
    {
        self::expectException(InvalidConfigurationException::class);
        self::expectExceptionMessageMatches('/Only the formats .+ are supported. Got .+./');

        (new Processor())->processConfiguration((new Configuration()), [
            'request_input' => [
                'formats' => $formats
            ]
        ]);
    }

    public static function invalidFormatsProvider(): iterable
    {
        yield [['js']];
        yield [['html']];
        yield [['json', 'xml', 'form', 'txt']];
        yield [['form', 'pdf']];
    }
}
