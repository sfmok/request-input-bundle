<?php

namespace Sfmok\RequestInput\EventListener;

use Sfmok\RequestInput\Exception\InputValidationException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class InputValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof InputValidationException) {
            return;
        }

        $event->setResponse($exception->getResponse());
    }
}
