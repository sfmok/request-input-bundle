<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Factory;

use Sfmok\RequestInput\Exception\UnexpectedFormatException;
use Sfmok\RequestInput\InputInterface;
use Sfmok\RequestInput\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final class InputFactory implements InputFactoryInterface
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function createFromRequest(Request $request, string $inputClass, string $format): InputInterface
    {
        switch ($format) {
            case 'json':
            case 'xml':
                $input = $this->serializer->deserialize($request->getContent(), $inputClass, $format);
                break;
            case 'form':
                $input = $this->serializer->denormalize($request->request->all(), $inputClass, $format);
                break;
            default:
                throw new UnexpectedFormatException(sprintf(
                    'The input format "%s" is not supported. Supported formats are : %s.',
                    $format,
                    implode(', ', self::INPUT_FORMATS)
                ));
        }

        $violations = $this->validator->validate($input);

        if ($violations->count()) {
            throw new ValidationException($violations);
        }

        return $input;
    }
}
