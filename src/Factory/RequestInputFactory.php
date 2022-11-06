<?php

namespace Sfmok\RequestInput\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sfmok\RequestInput\InputValidationInterface;
use Sfmok\RequestInput\Exception\InputValidationException;
use Sfmok\RequestInput\Serializer\RequestInputSerializer;
use Sfmok\RequestInput\RequestInputInterface;

final class RequestInputFactory implements RequestInputFactoryInterface
{
    private RequestInputSerializer $serializer;
    private ValidatorInterface $validator;

    public function __construct(RequestInputSerializer $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function createFromRequest(Request $request, string $inputClass): RequestInputInterface
    {
        /** @var RequestInputInterface $input */
        $input = $this->serializer->deserialize($request->getContent(), $inputClass, JsonEncoder::FORMAT);

        if (!$input instanceof InputValidationInterface) {
            return $input;
        }

        $violations = $this->validator->validate($input);

        if ($violations->count()) {
            throw new InputValidationException($violations);
        }

        return $input;
    }
}