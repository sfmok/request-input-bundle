<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Attribute\Input;

class InputTest extends TestCase
{
    public function testDefaultValues()
    {
        $input = new Input();
        $this->assertSame('json', $input->getFormat());
        $this->assertSame(['Default'], $input->getGroups());
        $this->assertSame([], $input->getContext());
    }

    public function testCustomValues()
    {
        $input = new Input('xml', ['foo'], ['groups' => ['foo']]);
        $this->assertSame('xml', $input->getFormat());
        $this->assertSame(['foo'], $input->getGroups());
        $this->assertSame(['groups' => ['foo']], $input->getContext());
    }
}
