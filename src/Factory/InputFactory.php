<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Factory;

use Sfmok\RequestInput\Exception\DeserializationException;
use Sfmok\RequestInput\Exception\ValidationException;
use Sfmok\RequestInput\Enum\Format;
use Sfmok\RequestInput\Enum\Source;
use Sfmok\RequestInput\Metadata\InputMetadataResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
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
        private InputMetadataResolverInterface $inputMetadataResolver,
    ) {}

    public function createFromRequest(Request $request, ?string $type): ?object
    {
        if (!$inputMetadata = $this->inputMetadataResolver->resolve($type)) {
            return null;
        }

        if ($inputMetadata->source->isQueryString()) {
            try {
                $data = json_encode($request->query->all(), JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new DeserializationException('Deserialization Failed', $exception);
            }
            $format = Format::Json;
        } else {
            $contentTypeFormat = $request->getContentTypeFormat();
            if (!$contentTypeFormat) {
                throw new UnsupportedMediaTypeHttpException('The "Content-Type" header must exist and not empty.');
            }

            $mimeType = $request->getMimeType($contentTypeFormat);

            if (!$mimeType || !($format = Format::tryFrom($contentTypeFormat))) {
                throw new UnsupportedMediaTypeHttpException(sprintf(
                    'The content-type "%s" is not supported. Supported MIME types are "%s".',
                    $mimeType,
                    implode('", "', $this->getSupportedMimeTypes($request)),
                ));
            }

            $data = $request->getContent();
        }

        try {
            $input = $this->serializer->deserialize($data, $type, $format->value, $inputMetadata->serialization->context);
        } catch (UnexpectedValueException $exception) {
            throw new DeserializationException('Deserialization Failed', $exception);
        }

        $validation = $inputMetadata->validation;

        if (!$validation->skip) {
            $violations = $this->validator->validate($input, null, $validation->groups);

            if ($violations->count()) {
                throw new ValidationException($violations, $validation->statusCode);
            }
        }

        return $input;
    }

    private function getSupportedMimeTypes(Request $request): array
    {
        $mimeTypes = [];
        foreach (Format::cases() as $format) {
            assert($format instanceof Format);
            $mimeTypes = array_merge($mimeTypes, $request->getMimeTypes($format->value));
        }

        return $mimeTypes;
    }
}
