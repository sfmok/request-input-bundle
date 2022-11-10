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
     * @dataProvider provideSupportsWithDefaultFormats
     */
    public function testSupportsWithDefaultFormats(bool $expected, ?string $contentType, string $inputClass): void
    {
        $request = new Request();
        $request->headers->set('Content-Type', $contentType);

        $argument = new ArgumentMetadata('foo', $inputClass, false, false, null);

        $resolver = $this->createArgumentResolver(InputFactoryInterface::INPUT_FORMATS);
        $this->assertSame($expected, $resolver->supports($request, $argument));
    }

    /**
     * @dataProvider provideSupportsWithCustomFormats
     */
    public function testSupportsWithCustomFormats(bool $expected, ?string $contentType, string $inputClass): void
    {
        $request = new Request();
        $request->headers->set('Content-Type', $contentType);

        $argument = new ArgumentMetadata('foo', $inputClass, false, false, null);

        $resolver = $this->createArgumentResolver(['json']);
        $this->assertSame($expected, $resolver->supports($request, $argument));
    }

    public function testResolveSucceeds(): void
    {
        $dummyInput = new DummyInput();

        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');

        $argument = new ArgumentMetadata('foo', DummyInput::class, false, false, null);

        $resolver = $this->createArgumentResolver([InputFactoryInterface::INPUT_FORMATS]);

        $this->inputFactory
            ->createFromRequest($request, $argument->getType(), $request->getContentType())
            ->shouldBeCalledOnce()
            ->willReturn($dummyInput)
        ;

        $this->assertEquals([$dummyInput], iterator_to_array($resolver->resolve($request, $argument)));
    }

    public function provideSupportsWithDefaultFormats(): iterable
    {
        yield [false, null, \stdClass::class];
        yield [false, 'application/rdf+xml', \stdClass::class];
        yield [false, 'text/html', DummyInput::class];
        yield [false, 'application/javascript', DummyInput::class];
        yield [false, 'text/plain', DummyInput::class];
        yield [false, 'application/ld+json', DummyInput::class];
        yield [false, 'application/json', \stdClass::class];
        yield [true, 'application/json', DummyInput::class];
        yield [true, 'application/xml', DummyInput::class];
        yield [false, 'application/xml', \stdClass::class];
        yield [false, 'multipart/form-data', \stdClass::class];
        yield [true, 'multipart/form-data', DummyInput::class];
    }

    public function provideSupportsWithCustomFormats(): iterable
    {
        yield [false, null, \stdClass::class];
        yield [false, 'application/rdf+xml', \stdClass::class];
        yield [false, 'text/html', DummyInput::class];
        yield [false, 'application/javascript', DummyInput::class];
        yield [false, 'text/plain', DummyInput::class];
        yield [false, 'application/ld+json', DummyInput::class];
        yield [false, 'application/json', \stdClass::class];
        yield [true, 'application/json', DummyInput::class];
        yield [false, 'application/xml', DummyInput::class];
        yield [false, 'application/xml', \stdClass::class];
        yield [false, 'multipart/form-data', \stdClass::class];
        yield [false, 'multipart/form-data', DummyInput::class];
    }

    private function createArgumentResolver(array $formats): InputArgumentResolver
    {
        return new InputArgumentResolver($this->inputFactory->reveal(), $formats);
    }
}