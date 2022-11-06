<?php

namespace Sfmok\RequestInput\ArgumentResolver;

use Sfmok\RequestInput\Factory\RequestInputFactoryInterface;
use Sfmok\RequestInput\RequestInputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class InputArgumentResolver implements ArgumentValueResolverInterface
{
    private RequestInputFactoryInterface $inputFactory;

    public function __construct(RequestInputFactoryInterface $inputFactory)
    {
        $this->inputFactory = $inputFactory;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_subclass_of($argument->getType(), RequestInputInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->inputFactory->createFromRequest($request, $argument->getType());
    }
}