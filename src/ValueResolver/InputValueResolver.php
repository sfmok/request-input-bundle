<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\ValueResolver;

use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class InputValueResolver implements ValueResolverInterface
{
    public function __construct(private InputFactoryInterface $inputFactory) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        return array_filter([$this->inputFactory->createFromRequest($request, $argument->getType())]);
    }
}
