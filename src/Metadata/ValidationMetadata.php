<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Metadata;

final readonly class ValidationMetadata
{
    /** @param list<string>|null $groups */
    public function __construct(
        public ?bool $skip = null,
        public ?int $statusCode = null,
        public ?array $groups = null,
    ) {}

    public static function mergeWithGlobal(?self $partial, self $global): self
    {
        return new self(
            $partial?->skip ?? $global->skip,
            $partial?->statusCode ?? $global->statusCode,
            $partial?->groups ?? ['Default'],
        );
    }
}
