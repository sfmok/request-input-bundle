<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Enum;

enum Source: string
{
    case BodyPayload = 'body_payload';
    case QueryString = 'query_string';

    public function isQueryString(): bool
    {
        return $this === self::QueryString;
    }
}
