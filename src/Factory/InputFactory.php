<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Factory;

use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Exception\DeserializationException;
use Sfmok\RequestInput\InputInterface;
use Sfmok\RequestInput\Exception\ValidationException;
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
        private bool $skipValidation,
        private array $inputFormats
    ) {
    }

    public function createFromRequest(Request $request, string $type): iterable
    {
        // @codeCoverageIgnoreStart
        if (\func_num_args() > 2) {
            @trigger_error("Third argument of 'InputFactory::createFromRequest' is not in use and is removed, however the argument in the callers code can be removed without side-effects.", \E_USER_DEPRECATED);
        }
        // @codeCoverageIgnoreEnd

        if (!is_subclass_of($type, InputInterface::class)) {
            return [];
        }

        $contentType = $request->headers->get('CONTENT_TYPE');
        if (null === $contentType || '' === $contentType) {
            throw new UnsupportedMediaTypeHttpException('The "Content-Type" header must exist and not empty.');
        }

        $inputMetadata = $request->attributes->get('_input');
        $formats = (array) ($inputMetadata?->getFormat() ?? $this->inputFormats);
        $supportedMimeTypes = $this->getSupportedMimeTypes($request, $formats);

        if (!\in_array($contentType, $supportedMimeTypes, true)) {
            throw new UnsupportedMediaTypeHttpException(sprintf('The content-type "%s" is not supported. Supported MIME types are "%s".', $contentType, implode('", "', $supportedMimeTypes)));
        }

        $data = $request->getContent();
        $format = $request->getContentTypeFormat();
        if (Input::INPUT_FORM_FORMAT === $format) {
            @trigger_error("The format 'form' is deprecated and will be removed in version 2.0. Use 'symfony/form' component instead.", \E_USER_DEPRECATED);
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

        return [$input];
    }

    private function getSupportedMimeTypes(Request $request, array $formats): array
    {
        $mimeTypes = [];
        foreach ($formats as $format) {
            $mimeTypes = array_merge($mimeTypes, $request->getMimeTypes($format));
        }

        return $mimeTypes;
    }
}
