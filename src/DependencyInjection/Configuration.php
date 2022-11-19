<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\DependencyInjection;

use Sfmok\RequestInput\Attribute\Input;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                    ->defaultValue(Input::INPUT_SUPPORTED_FORMATS)
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->end()
                    ->validate()
                        ->ifTrue(function ($values) {
                            foreach ($values as $value) {
                                if (!\in_array($value, Input::INPUT_SUPPORTED_FORMATS)) {
                                    return true;
                                }
                            }
                            return false;
                        })
                        ->thenInvalid(sprintf('Only the formats [%s] are supported. Got %s.', implode(', ', Input::INPUT_SUPPORTED_FORMATS), '%s'))
                    ->end()
                ->end()
                ->booleanNode('skip_validation')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
