<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\EventListener\ReadInputListener;
use Sfmok\RequestInput\Metadata\InputMetadataFactoryInterface;
use Sfmok\RequestInput\Tests\Fixtures\Controller\TestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ReadInputListenerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $inputMetadataFactory;

    protected function setUp(): void
    {
        $this->inputMetadataFactory = $this->prophesize(InputMetadataFactoryInterface::class);
        $this->httpKernel = $this->prophesize(HttpKernelInterface::class);
    }

    public function testOnKernelController(): void
    {
        $request = new Request();
        $inputMetadata = new Input();
        $event = $this->getControllerEvent($request);
        $this->inputMetadataFactory
            ->createInputMetadata($event->getController())
            ->willReturn($inputMetadata)
            ->shouldBeCalledOnce()
        ;

        $listener = new ReadInputListener($this->inputMetadataFactory->reveal(), true);

        $listener->onKernelController($event);

        $this->assertSame($inputMetadata, $request->attributes->get('_input'));
    }

    public function testOnKernelControllerWithoutInput(): void
    {
        $request = new Request();
        $event = $this->getControllerEvent($request);
        $this->inputMetadataFactory
            ->createInputMetadata($event->getController())
            ->willReturn(null)
            ->shouldBeCalledOnce()
        ;

        $listener = new ReadInputListener($this->inputMetadataFactory->reveal(), true);

        $listener->onKernelController($event);

        $this->assertFalse($request->attributes->has('_input'));
    }

    public function testOnKernelControllerWithNonEnabled(): void
    {
        $event = $this->getControllerEvent(new Request());
        $this->inputMetadataFactory->createInputMetadata()->shouldNotBeCalled();
        $listener = new ReadInputListener($this->inputMetadataFactory->reveal(), false);

        $listener->onKernelController($event);
    }

    private function getControllerEvent(Request $request): ControllerEvent
    {
        return new ControllerEvent(
            $this->httpKernel->reveal(),
            [new TestController(), 'testWithInput'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}