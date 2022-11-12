<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Attribute;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Input
{
    public const INPUT_JSON_FORMAT = 'json';
    public const INPUT_XML_FORMAT = 'xml';
    public const INPUT_FORM_FORMAT = 'form';
    public const INPUT_SUPPORTED_FORMATS = [self::INPUT_JSON_FORMAT, self::INPUT_XML_FORMAT, self::INPUT_FORM_FORMAT];

    public function __construct(
        private string $format = 'json',
        private array $groups = ['Default'],
        private array $context = []
    ) {
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
