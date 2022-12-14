<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Metadata;

use Sfmok\RequestInput\Attribute\Input;

final class InputMetadataFactory implements InputMetadataFactoryInterface
{
    public function createInputMetadata(string|object|array $controller): ?Input
    {
        if (\is_array($controller)) {
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (\is_object($controller) && !$controller instanceof \Closure) {
            $reflection = new \ReflectionMethod($controller, '__invoke');
        } elseif (\is_string($controller) && \str_contains($controller, '::')) {
            $reflection = new \ReflectionMethod($controller);
        } else {
            return null;
        }

        $input = null;
        if (null !== $refInput = $reflection->getAttributes(Input::class)[0] ?? null) {
            $input = $refInput->newInstance();
        }

        return $input;
    }
}
