<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\DependencyInjection;

use Sfmok\RequestInput\EventListener\ExceptionListener;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Metadata\InputMetadataResolver;
use Sfmok\RequestInput\Metadata\InputMetadataResolverInterface;
use Sfmok\RequestInput\Metadata\SerializationMetadata;
use Sfmok\RequestInput\Metadata\ValidationMetadata;
use Sfmok\RequestInput\ValueResolver\InputValueResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
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

        $globalValidation = $this->createGlobalValidationDefinition($config['validation']);
        $globalSerialization = $this->createGlobalSerializationDefinition($config['serialization']);

        $container->register(InputMetadataResolver::class)
            ->setArguments([
                '$globalValidation' => $globalValidation,
                '$globalSerialization' => $globalSerialization,
            ])
            ->setPublic(false)
        ;

        $container->setAlias(InputMetadataResolverInterface::class, InputMetadataResolver::class)->setPublic(false);

        $container->register(InputFactory::class)
            ->setArguments([
                '$serializer' => new Reference(SerializerInterface::class),
                '$validator' => new Reference(ValidatorInterface::class),
                '$inputMetadataResolver' => new Reference(InputMetadataResolverInterface::class),
            ])
            ->setPublic(false)
        ;

        $container->setAlias(InputFactoryInterface::class, InputFactory::class)->setPublic(false);

        $container->register(InputValueResolver::class)
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
    }

    /**
     * @param array{skip: bool, status_code: int} $validation
     */
    private function createGlobalValidationDefinition(array $validation): Definition
    {
        return (new Definition(ValidationMetadata::class))
            ->setArguments([
                $validation['skip'],
                $validation['status_code'],
                null,
            ]);
    }

    /**
     * @param array<string, mixed> $serialization
     */
    private function createGlobalSerializationDefinition(array $serialization): Definition
    {
        return (new Definition(SerializationMetadata::class))
            ->setArguments([
                $serialization['context'],
            ]);
    }
}
