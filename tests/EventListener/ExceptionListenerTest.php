<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sfmok\RequestInput\EventListener\ExceptionListener;
use Sfmok\RequestInput\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class ExceptionListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testOnKernelException(): void
    {
        $serializedConstraintViolationList = '{"foo": "bar"}';
        $list = new ConstraintViolationList([]);

        $serializerProphecy = $this->prophesize(SerializerInterface::class);
        $serializerProphecy->serialize($list, 'json')->willReturn($serializedConstraintViolationList)->shouldBeCalled();

        $listener = new ExceptionListener($serializerProphecy->reveal());
        $event = new ExceptionEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new ValidationException($list)
        );

        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($serializedConstraintViolationList, $response->getContent());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
    }
}