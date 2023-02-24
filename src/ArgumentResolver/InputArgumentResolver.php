<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\ArgumentResolver;

use Sfmok\RequestInput\InputInterface;
use Symfony\Component\HttpFoundation\Request;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

class InputArgumentResolver implements ArgumentValueResolverInterface
{
    public function __construct(private InputFactoryInterface $inputFactory)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_subclass_of($argument->getType(), InputInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->inputFactory->createFromRequest($request, $argument->getType(), $request->getContentType());
    }
}
