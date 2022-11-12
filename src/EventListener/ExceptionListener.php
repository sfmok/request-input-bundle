<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\EventListener;

use Sfmok\RequestInput\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionListener
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ValidationException) {
            return;
        }

        $event->setResponse(new Response(
            $this->serializer->serialize($exception->getViolationList(), 'json'),
            $exception->getStatusCode(),
            ['Content-Type' => 'application/problem+json; charset=utf-8']
        ));
    }
}
