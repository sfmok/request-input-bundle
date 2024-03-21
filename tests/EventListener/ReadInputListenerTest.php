<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\EventListener\ReadInputListener;
use Sfmok\RequestInput\Exception\UnexpectedFormatException;
use Sfmok\RequestInput\Metadata\InputMetadataFactory;
use Sfmok\RequestInput\Tests\Fixtures\Controller\TestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ReadInputListenerTest extends TestCase
{
    public function testOnKernelController(): void
    {
        $request = new Request();
        $event = $this->getControllerEvent($request, 'testWithInput');
        $listener = new ReadInputListener(new InputMetadataFactory());
        $listener->onKernelController($event);

        self::assertTrue($request->attributes->has('_input'));
        self::assertInstanceOf(Input::class, $request->attributes->get('_input'));
    }

    public function testOnKernelControllerWithoutInput(): void
    {
        $request = new Request();
        $event = $this->getControllerEvent($request, 'testWithoutInput');
        $listener = new ReadInputListener(new InputMetadataFactory());
        $listener->onKernelController($event);

        self::assertFalse($request->attributes->has('_input'));
    }

    public function testOnKernelControllerWithUnsupportedFormat(): void
    {
        self::expectException(UnexpectedFormatException::class);
        self::expectExceptionMessageMatches('/Only the formats .+ are supported. Got .+./');

        $request = new Request();
        $event = $this->getControllerEvent($request, 'testWithInputUnsupportedFormat');
        $listener = new ReadInputListener(new InputMetadataFactory());
        $listener->onKernelController($event);
    }

    private function getControllerEvent(Request $request, string $method): ControllerEvent
    {
        return new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            [new TestController(), $method],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
