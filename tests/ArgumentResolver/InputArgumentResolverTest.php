<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\ArgumentResolver;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sfmok\RequestInput\ArgumentResolver\InputArgumentResolver;
use Sfmok\RequestInput\Attribute\Input;
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

    public function testSupportsWithArgumentTypeNotInput(): void
    {
        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');
        $argument = new ArgumentMetadata('foo', \stdClass::class, false, false, null);

        $resolver = $this->createArgumentResolver(Input::INPUT_SUPPORTED_FORMATS);
        $this->assertFalse($resolver->supports($request, $argument));
    }

    /**
     * @dataProvider provideSupportsWithDefaultGlobalFormats
     */
    public function testSupportsWithDefaultGlobalFormats(bool $expected, ?string $contentType): void
    {
        $request = new Request();
        $request->headers->set('Content-Type', $contentType);
        $argument = new ArgumentMetadata('foo', DummyInput::class, false, false, null);

        $resolver = $this->createArgumentResolver(Input::INPUT_SUPPORTED_FORMATS);
        $this->assertSame($expected, $resolver->supports($request, $argument));
    }

    /**
     * @dataProvider provideSupportsWithCustomGlobalFormats
     */
    public function testSupportsWithCustomGlobalFormats(bool $expected, ?string $contentType): void
    {
        $request = new Request();
        $request->headers->set('Content-Type', $contentType);

        $argument = new ArgumentMetadata('foo', DummyInput::class, false, false, null);

        $resolver = $this->createArgumentResolver(['json']);
        $this->assertSame($expected, $resolver->supports($request, $argument));
    }

    /**
     * @dataProvider provideSupportsWithCustomFormatsInInputAttribute
     */
    public function testSupportsWithCustomFormatsInInputAttribute(bool $expected, ?string $contentType): void
    {
        $request = new Request();
        $request->headers->set('Content-Type', $contentType);
        $request->attributes->set('_input', new Input('xml'));

        $argument = new ArgumentMetadata('foo', DummyInput::class, false, false, null);

        $resolver = $this->createArgumentResolver(Input::INPUT_SUPPORTED_FORMATS);
        $this->assertSame($expected, $resolver->supports($request, $argument));
    }

    public function testResolveSucceeds(): void
    {
        $dummyInput = new DummyInput();

        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');

        $argument = new ArgumentMetadata('foo', DummyInput::class, false, false, null);

        $resolver = $this->createArgumentResolver([Input::INPUT_SUPPORTED_FORMATS]);

        $this->inputFactory
            ->createFromRequest($request, $argument->getType(), $request->getContentType())
            ->shouldBeCalledOnce()
            ->willReturn($dummyInput)
        ;

        $this->assertEquals([$dummyInput], iterator_to_array($resolver->resolve($request, $argument)));
    }

    public function provideSupportsWithDefaultGlobalFormats(): iterable
    {
        yield [false, null];
        yield [false, 'application/rdf+xml'];
        yield [false, 'text/html'];
        yield [false, 'application/javascript'];
        yield [false, 'text/plain'];
        yield [false, 'application/ld+json'];
        yield [true, 'application/json'];
        yield [true, 'application/xml'];
        yield [true, 'multipart/form-data'];
    }

    public function provideSupportsWithCustomGlobalFormats(): iterable
    {
        yield [false, null];
        yield [false, 'application/rdf+xml'];
        yield [false, 'text/html'];
        yield [false, 'application/javascript'];
        yield [false, 'text/plain'];
        yield [false, 'application/ld+json'];
        yield [true, 'application/json'];
        yield [false, 'application/xml'];
        yield [false, 'multipart/form-data'];
    }

    public function provideSupportsWithCustomFormatsInInputAttribute(): iterable
    {
        yield [false, null];
        yield [false, 'application/rdf+xml'];
        yield [false, 'text/html'];
        yield [false, 'application/javascript'];
        yield [false, 'text/plain'];
        yield [false, 'application/ld+json'];
        yield [false, 'application/json'];
        yield [true, 'application/xml'];
        yield [false, 'multipart/form-data'];
    }

    private function createArgumentResolver(array $formats): InputArgumentResolver
    {
        return new InputArgumentResolver($this->inputFactory->reveal(), $formats);
    }
}