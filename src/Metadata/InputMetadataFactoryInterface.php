<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Metadata;

use Sfmok\RequestInput\Attribute\Input;

interface InputMetadataFactoryInterface
{
    public function createInputMetadata(array|object|string $controller): ?Input;
}
