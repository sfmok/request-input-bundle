<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\EventListener;

use Sfmok\RequestInput\Exception\ExceptionInterface;
use Sfmok\RequestInput\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionListener
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ExceptionInterface) {
            return;
        }

        if ($exception instanceof ValidationException) {
            $event->setResponse(new Response(
                $this->serializer->serialize($exception->getViolationList(), 'json'),
                $exception->getStatusCode(),
                ['Content-Type' => 'application/problem+json; charset=utf-8']
            ));
        }

        $errors = [];
        $previous = $exception->getPrevious();

        if ($previous instanceof PartialDenormalizationException) {
            $errors =  $previous->getErrors();
        }

        if ($previous instanceof NotNormalizableValueException) {
            $errors = [$previous];
        }

        $violations = [];
        /** @var NotNormalizableValueException $error */
        foreach ($errors as $error) {
            $violations[] = [
                'propertyPath' => $error->getPath(),
                'message' => sprintf('This value should be of type \'%s\'', $error->getExpectedTypes()[0]),
                'current_type' => $error->getCurrentType(),
            ];
        }

        $event->setResponse(new Response(
            json_encode($violations),
            $exception->getStatusCode(),
            ['Content-Type' => 'application/problem+json; charset=utf-8']
        ));
    }
}
