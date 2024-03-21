<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\EventListener;

use Sfmok\RequestInput\Exception\ExceptionInterface;
use Sfmok\RequestInput\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
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

        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        if ($exception instanceof ValidationException) {
            $event->setResponse(new Response(
                $this->serializer->serialize($exception->getViolationList(), 'json'),
                $exception->getStatusCode(),
                $headers
            ));

            return;
        }

        $previous = $exception->getPrevious();
        $detail = $previous->getMessage();

        $violations = [];
        $errors = \method_exists($previous, 'getErrors') ? $previous->getErrors() : [$previous];
        foreach ($errors as $error) {
            if ($error instanceof NotNormalizableValueException) {
                $violations[] = [
                    'propertyPath' => $error->getPath(),
                    'message' => sprintf('This value should be of type %s', $error->getExpectedTypes()[0]),
                    'currentType' => $error->getCurrentType(),
                ];
            }
        }

        $data = json_encode([
            'title' => $exception->getMessage(),
            'detail' => empty($violations) ? $detail : 'Data error',
            'violations' => $violations
        ]);

        $event->setResponse(new Response($data, $exception->getStatusCode(), $headers));
    }
}
