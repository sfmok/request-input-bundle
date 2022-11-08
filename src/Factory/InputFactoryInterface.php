<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Factory;

use Sfmok\RequestInput\InputInterface;
use Symfony\Component\HttpFoundation\Request;

interface InputFactoryInterface
{
    public const INPUT_FORMATS = ['json', 'xml', 'form'];

    public function createFromRequest(Request $request, string $inputClass, string $format): InputInterface;
}
