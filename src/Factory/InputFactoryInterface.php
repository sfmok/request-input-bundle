<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Factory;

use Symfony\Component\HttpFoundation\Request;

interface InputFactoryInterface
{
    public function createFromRequest(Request $request, ?string $type): ?object;
}
