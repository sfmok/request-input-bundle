<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Enum;

enum Format: string
{
    case Json = 'json';
    case Xml = 'xml';
}
