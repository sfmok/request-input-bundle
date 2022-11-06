<?php

namespace Sfmok\RequestInput\Factory;

use Symfony\Component\HttpFoundation\Request;
use Sfmok\RequestInput\RequestInputInterface;

interface RequestInputFactoryInterface
{
    public function createFromRequest(Request $request, string $inputClass): RequestInputInterface;
}