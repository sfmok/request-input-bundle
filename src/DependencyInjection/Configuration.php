<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('request_input');
        $root = $treeBuilder->getRootNode();

        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                ->end()
                ->arrayNode('validation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('skip')
                            ->defaultFalse()
                        ->end()
                        ->integerNode('status_code')
                            ->min(100)
                            ->max(599)
                            ->defaultValue(400)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('serialization')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('context')
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
