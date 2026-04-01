<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Metadata;

use Sfmok\RequestInput\Attribute\AsInput;

interface InputMetadataResolverInterface
{
    public function resolve(?string $className): ?AsInput;
}
