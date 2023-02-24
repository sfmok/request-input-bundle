<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Factory;

use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Exception\DeserializationException;
use Sfmok\RequestInput\Exception\UnexpectedFormatException;
use Sfmok\RequestInput\InputInterface;
use Sfmok\RequestInput\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final class InputFactory implements InputFactoryInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private bool $skipValidation,
        private array $inputFormats
    ) {
    }

    public function createFromRequest(Request $request, string $type, ?string $format): InputInterface
    {
        $inputMetadata = $request->attributes->get('_input');
        $supportedFormats = (array) ($inputMetadata?->getFormat() ?? $this->inputFormats);

        if (!\in_array($format, $supportedFormats, true)) {
            throw new UnexpectedFormatException(sprintf(
                'Unexpected request content type, expected any of [%s]. Got "%s".',
                implode(', ', $this->getExpectedContentTypes($request, $supportedFormats)),
                $request->getMimeType($format ?? '')
            ));
        }

        $data = $request->getContent();
        if (Input::INPUT_FORM_FORMAT === $format) {
            $data = json_encode($request->request->all());
            $format = Input::INPUT_JSON_FORMAT;
        }

        try {
            $input = $this->serializer->deserialize($data, $type, $format, $inputMetadata?->getContext() ?? []);
        } catch (UnexpectedValueException $exception) {
            throw new DeserializationException('Deserialization Failed', $exception);
        }


        if (!$this->skipValidation) {
            $violations = $this->validator->validate($input, null, $inputMetadata?->getGroups() ?? ['Default']);

            if ($violations->count()) {
                throw new ValidationException($violations);
            }
        }

        return $input;
    }

    private function getExpectedContentTypes(Request $request, array $expectedFormats): array
    {
        $expectedContentTypes = [];
        foreach ($expectedFormats as $format) {
            $expectedContentTypes = [...$expectedContentTypes, ...$request->getMimeTypes($format)];
        }

        return $expectedContentTypes;
    }
}
