<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\ValueResolver;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInput;
use Sfmok\RequestInput\ValueResolver\InputValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class InputValueResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $dummyInput = new DummyInput();
        $request = new Request();
        $argument = new ArgumentMetadata('input', $dummyInput::class, false, false, null);
        $factory = $this->createMock(InputFactoryInterface::class);
        $resolver = new InputValueResolver($factory);
        $factory->expects(self::once())
            ->method('createFromRequest')
            ->with($request, $dummyInput::class)
            ->willReturn([$dummyInput]);
        $this->assertEquals([$dummyInput], $resolver->resolve($request, $argument));
    }
}
