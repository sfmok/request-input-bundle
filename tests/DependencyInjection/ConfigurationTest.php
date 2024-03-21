<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfig(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();
        $config = $this->processor->processConfiguration($this->configuration, []);

        $this->assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertEquals([
            'enabled' => true,
            'formats' => ['json', 'xml', 'form'],
            'skip_validation' => false
        ], $config);

    }

    public function invalidFormatsProvider(): iterable
    {
        yield [['js']];
        yield [['html']];
        yield [['json', 'xml', 'form', 'txt']];
        yield [['form', 'pdf']];
    }

    /**
     * @dataProvider invalidFormatsProvider
     */
    public function testInvalidFormatsConfig(array $formats): void
    {
        self::expectException(InvalidConfigurationException::class);
        self::expectExceptionMessageMatches('/Only the formats .+ are supported. Got .+./');

        $this->processor->processConfiguration($this->configuration, [
            'request_input' => [
                'formats' => $formats
            ]
        ]);
    }
}
