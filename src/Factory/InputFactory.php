<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Factory;

use Sfmok\RequestInput\InputInterface;
use Sfmok\RequestInput\ValidationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sfmok\RequestInput\Serializer\InputSerializer;
use Sfmok\RequestInput\Exception\InputValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class InputFactory implements InputFactoryInterface
{
    private InputSerializer $serializer;
    private ValidatorInterface $validator;

    public function __construct(InputSerializer $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function createFromRequest(Request $request, string $inputClass): InputInterface
    {
        /** @var InputInterface $input */
        $input = $this->serializer->deserialize($request->getContent(), $inputClass, JsonEncoder::FORMAT);

        if (!$input instanceof ValidationInterface) {
            return $input;
        }

        $violations = $this->validator->validate($input);

        if ($violations->count()) {
            throw new InputValidationException($violations);
        }

        return $input;
    }
}