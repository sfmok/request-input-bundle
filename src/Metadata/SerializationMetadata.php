<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Metadata;

final readonly class SerializationMetadata
{
    /** @param array<string, mixed> $context */
    public function __construct(
        public array $context = [],
    ) {}

    public static function mergeWithGlobal(?self $partial, self $global): self
    {
        return new self(
            array_merge($global->context, $partial?->context ?? []),
        );
    }
}
