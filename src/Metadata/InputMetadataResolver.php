<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Metadata;

use Sfmok\RequestInput\Enum\Source;
use Sfmok\RequestInput\Attribute\AsInput;

/** @internal */
final class InputMetadataResolver implements InputMetadataResolverInterface
{
    public function __construct(
        private ValidationMetadata $globalValidation,
        private SerializationMetadata $globalSerialization,
    ) {}

    public function resolve(?string $className): ?AsInput
    {
        if (!$attribute = $this->getAttribute($className)) {
            return null;
        }

        return new AsInput(
            source: $attribute->source ?? Source::BodyPayload,
            validation: ValidationMetadata::mergeWithGlobal($attribute->validation, $this->globalValidation),
            serialization: SerializationMetadata::mergeWithGlobal($attribute->serialization, $this->globalSerialization),
        );
    }

    private function getAttribute(?string $className): ?AsInput
    {
        if (!$className || !class_exists($className)) {
            return null;
        }

        try {
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException) {
            return null;
        }

        $attributes = $reflection->getAttributes(AsInput::class);

        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}
