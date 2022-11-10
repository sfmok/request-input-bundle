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
    private InputFactoryInterface $inputFactory;
    private array $inputFormats;
    private bool $enabled;

    public function __construct(InputFactoryInterface $inputFactory, array $inputFormats, bool $enabled = true)
    {
        $this->inputFactory = $inputFactory;
        $this->inputFormats = $inputFormats;
        $this->enabled = $enabled;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!$this->enabled || !\in_array($request->getContentType(), $this->inputFormats)) {
            return false;
        }

        return is_subclass_of($argument->getType(), InputInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->inputFactory->createFromRequest($request, $argument->getType(), $request->getContentType());
    }
}
