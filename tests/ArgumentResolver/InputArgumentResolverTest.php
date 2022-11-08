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
     * @dataProvider provideSupports
     */
    public function testSupports(bool $expected, Request $request, ArgumentMetadata $argument): void
    {
        $resolver = $this->createArgumentResolver();
        $this->assertSame($expected, $resolver->supports($request, $argument));
    }

    public function testResolveSucceeds(): void
    {
        $dummyInput = new DummyInput();
        $resolver = $this->createArgumentResolver();
        $argument = new ArgumentMetadata('foo', $dummyInput::class, false, false, null);
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json']);

        $this->inputFactory
            ->createFromRequest($request, $argument->getType(), $request->getContentType())
            ->shouldBeCalledOnce()
            ->willReturn($dummyInput)
        ;

        $this->assertEquals([$dummyInput], iterator_to_array($resolver->resolve($request, $argument)));
    }

    public function provideSupports(): iterable
    {
        yield [false, new Request(), new ArgumentMetadata('foo', \stdClass::class, false, false, null)];
        yield [false, new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/rdf+xml']), new ArgumentMetadata('foo', \stdClass::class, false, false, null)];
        yield [false, new Request([], [], [], [], [], ['CONTENT_TYPE' => 'text/html']), new ArgumentMetadata('foo', DummyInput::class, false, false, null)];
        yield [false, new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/javascript']), new ArgumentMetadata('foo', DummyInput::class, false, false, null)];
        yield [false, new Request([], [], [], [], [], ['CONTENT_TYPE' => 'text/plain']), new ArgumentMetadata('foo', DummyInput::class, false, false, null)];
        yield [true, new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json']), new ArgumentMetadata('foo', DummyInput::class, false, false, null)];
        yield [false, new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/ld+json']), new ArgumentMetadata('foo', DummyInput::class, false, false, null)];
        yield [true, new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/xml']), new ArgumentMetadata('foo', DummyInput::class, false, false, null)];
        yield [true, new Request([], [], [], [], [], ['CONTENT_TYPE' => 'multipart/form-data']), new ArgumentMetadata('foo', DummyInput::class, false, false, null)];
    }

    private function createArgumentResolver(): InputArgumentResolver
    {
        return new InputArgumentResolver($this->inputFactory->reveal());
    }
}