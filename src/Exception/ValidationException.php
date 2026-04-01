<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends HttpException implements ExceptionInterface
{
    public function __construct(
        private ConstraintViolationListInterface $violationList,
        int $statusCode = 400,
    ) {
        parent::__construct($statusCode);
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}
