<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\DependencyInjection;

use Sfmok\RequestInput\ArgumentResolver\InputArgumentResolver;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Metadata\InputMetadataFactory;
use Sfmok\RequestInput\Metadata\InputMetadataFactoryInterface;
use Sfmok\RequestInput\EventListener\ReadInputListener;
use Sfmok\RequestInput\EventListener\ExceptionListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
* @internal
*/
class RequestInputExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!$config['enabled']) {
            return;
        }

        $container->register(InputFactory::class)
            ->setArguments([
                '$serializer' => new Reference(SerializerInterface::class),
                '$validator' => new Reference(ValidatorInterface::class),
                '$skipValidation' => $config['skip_validation'],
                '$inputFormats' => $config['formats'],
            ])
            ->setPublic(false)
        ;

        $container->register(InputMetadataFactory::class)->setPublic(false);

        $container->setAlias(InputFactoryInterface::class, InputFactory::class)->setPublic(false);
        $container->setAlias(InputMetadataFactoryInterface::class, InputMetadataFactory::class)->setPublic(false);

        $container->register(InputArgumentResolver::class)
            ->setArguments([
                '$inputFactory' => new Reference(InputFactoryInterface::class),
            ])
            ->addTag('controller.argument_value_resolver', ['priority' => 40])
            ->setPublic(false)
        ;

        $container->register(ExceptionListener::class)
            ->setArguments(['$serializer' => new Reference(SerializerInterface::class)])
            ->addTag('kernel.event_listener', ['event' => 'kernel.exception'])
            ->setPublic(false)
        ;

        $container->register(ReadInputListener::class)
            ->setArguments(['$inputMetadataFactory' => new Reference(InputMetadataFactoryInterface::class)])
            ->addTag('kernel.event_listener', ['event' => 'kernel.controller'])
            ->setPublic(false)
        ;
    }
}
