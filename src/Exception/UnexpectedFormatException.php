<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UnexpectedFormatException extends BadRequestHttpException
{
}
