<?php

namespace Sfmok\RequestInput\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InputValidationException extends BadRequestHttpException
{
    private array $errors = [];

    public function __construct(ConstraintViolationListInterface $violationList)
    {
        parent::__construct();
        $this->errors = $this->convertViolationListToArray($violationList);
    }

    public function getResponse(): JsonResponse
    {
        return new JsonResponse(['errors' => $this->errors]);
    }

    private function convertViolationListToArray(ConstraintViolationListInterface $violationList): array
    {
        $errors = [];

        foreach ($violationList as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
                'code' => $violation->getCode(),
            ];
        }

        return $errors;
    }
}