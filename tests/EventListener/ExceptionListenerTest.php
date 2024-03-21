<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\EventListener\ExceptionListener;
use Sfmok\RequestInput\Exception\DeserializationException;
use Sfmok\RequestInput\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class ExceptionListenerTest extends TestCase
{
    private SerializerInterface $serializer;
    private HttpKernelInterface $httpKernel;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->httpKernel = $this->createMock(HttpKernelInterface::class);
    }

    public function testOnKernelExceptionWithValidationException(): void
    {
        $serializedConstraintViolationList = '{"foo": "bar"}';
        $list = new ConstraintViolationList([]);

        $this->serializer->expects(self::once())
            ->method('serialize')
            ->with($list, 'json')
            ->willReturn($serializedConstraintViolationList)
        ;

        $listener = new ExceptionListener($this->serializer);
        $event = new ExceptionEvent(
            $this->httpKernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new ValidationException($list)
        );

        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($serializedConstraintViolationList, $response->getContent());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf-8', $response->headers->get('Content-Type'));
    }

    public function testOnKernelExceptionWithDenormalizationExceptionDataError(): void
    {
        $previous = NotNormalizableValueException::createForUnexpectedDataType('test', [], ['string'], 'foo');
        $listener = new ExceptionListener($this->serializer);
        $event = new ExceptionEvent(
            $this->httpKernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new DeserializationException('Deserialization Failed', $previous)
        );

        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame(json_encode([
            'title' => 'Deserialization Failed',
            'detail' => 'Data error',
            'violations' => [[
                'propertyPath' => 'foo',
                'message' => 'This value should be of type string',
                'currentType' => 'array'
            ]],
        ]), $response->getContent());
    }

    public function testOnKernelExceptionWithDenormalizationExceptionSyntaxError(): void
    {
        $previous = new NotEncodableValueException('Syntax error');
        $listener = new ExceptionListener($this->serializer);
        $event = new ExceptionEvent(
            $this->httpKernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new DeserializationException('Deserialization Failed', $previous)
        );

        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame(json_encode([
            'title' => 'Deserialization Failed',
            'detail' => 'Syntax error',
            'violations' => [],
        ]), $response->getContent());
    }

    public function testOnKernelExceptionWithoutValidationException(): void
    {
        $this->serializer->expects(self::never())->method('deserialize');

        $listener = new ExceptionListener($this->serializer);
        $event = new ExceptionEvent(
            $this->httpKernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new \Exception()
        );

        $listener->onKernelException($event);
    }
}
