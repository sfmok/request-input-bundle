<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\ArgumentResolver;

use Sfmok\RequestInput\InputInterface;
use Sfmok\RequestInput\Attribute\Input;
use Symfony\Component\HttpFoundation\Request;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

class InputArgumentResolver implements ArgumentValueResolverInterface
{
    public function __construct(private InputFactoryInterface $inputFactory, private array $inputFormats)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!is_subclass_of($argument->getType(), InputInterface::class)) {
            return false;
        }

        /** @var Input|null $inputAttribute */
        if ($inputAttribute = $request->attributes->get('_input')) {
            $this->inputFormats = [$inputAttribute->getFormat()];
        }

        return \in_array($request->getContentType(), $this->inputFormats);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->inputFactory->createFromRequest($request, $argument->getType(), $request->getContentType());
    }
}
