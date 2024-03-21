<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Fixtures\Controller;

use Sfmok\RequestInput\Attribute\Input;

class TestController
{
    #[Input(format: 'json', groups: ['bar'], context: ['groups' => ['bar']])]
    public function __invoke(): void
    {
    }

    #[Input(format: 'json', groups: ['foo'], context: ['groups' => ['foo']])]
    public function testWithInput(): void
    {
    }

    public function testWithoutInput(): void
    {
    }

    #[Input(format: 'unsupported')]
    public function testWithInputUnsupportedFormat(): void
    {
    }
}