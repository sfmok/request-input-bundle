<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Sfmok\RequestInput\Factory\InputFactoryInterface;

/**
 * Configuration.
 *
 * @internal
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('request_input');
        $root = $treeBuilder->getRootNode();

        $root
            ->fixXmlConfig('format', 'formats')
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                ->end()
                ->arrayNode('formats')
                    ->defaultValue(InputFactoryInterface::INPUT_FORMATS)
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->end()
                    ->validate()
                        ->ifTrue(function ($values) {
                            foreach ($values as $value) {
                                if (!\in_array($value, InputFactoryInterface::INPUT_FORMATS)) {
                                    return true;
                                }
                            }
                            return false;
                        })
                        ->thenInvalid(sprintf('Only the formats %s are supported. Got %s.', implode(', ', InputFactoryInterface::INPUT_FORMATS), '%s'))
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
