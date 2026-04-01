<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Attribute;

use Sfmok\RequestInput\Enum\Source;
use Sfmok\RequestInput\Metadata\SerializationMetadata;
use Sfmok\RequestInput\Metadata\ValidationMetadata;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsInput
{
    public function __construct(
        public ?Source $source = null,
        public ?ValidationMetadata $validation = null,
        public ?SerializationMetadata $serialization = null,
    ) {}
}
