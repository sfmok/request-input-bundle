<?php

namespace Sfmok\RequestInput\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DeserializationException extends BadRequestHttpException implements ExceptionInterface
{
    public function __construct(string $message, \Throwable $previous)
    {
        parent::__construct($message, $previous);
    }
}
