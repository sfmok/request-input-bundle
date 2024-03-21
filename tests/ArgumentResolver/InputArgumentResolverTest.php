<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\ArgumentResolver;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sfmok\RequestInput\ArgumentResolver\InputArgumentResolver;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class InputArgumentResolverTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $inputFactory;

    protected function setUp(): void
    {
        $this->inputFactory = $this->prophesize(InputFactoryInterface::class);
    }

    /**
     * @dataProvider provideSupportData
     */
    public function testSupports(mixed $type, bool $expectedResult): void
    {
        $argument = new ArgumentMetadata('foo', $type, false, false, null);
        $this->assertSame($expectedResult, $this->createArgumentResolver()->supports(new Request(), $argument));
    }

    public function testResolve(): void
    {
        $dummyInput = new DummyInput();
        $request = new Request();
        $argument = new ArgumentMetadata('foo', $dummyInput::class, false, false, null);

        $this->inputFactory
            ->createFromRequest($request, $dummyInput::class, $request->getContentType())
            ->shouldBeCalledOnce()
            ->willReturn($dummyInput)
        ;

        $resolver = $this->createArgumentResolver();
        $this->assertEquals([$dummyInput], iterator_to_array($resolver->resolve($request, $argument)));
    }

    public function provideSupportData(): iterable
    {
        yield [null, false];
        yield [\stdClass::class, false];
        yield ["Foo", false];
        yield [DummyInput::class, true];
    }

    private function createArgumentResolver(): InputArgumentResolver
    {
        return new InputArgumentResolver($this->inputFactory->reveal());
    }
}