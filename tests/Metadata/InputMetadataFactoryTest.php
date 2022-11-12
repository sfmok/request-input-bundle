<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Metadata\InputMetadataFactory;
use Sfmok\RequestInput\Tests\Fixtures\Controller\TestController;

class InputMetadataFactoryTest extends TestCase
{
    private InputMetadataFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new InputMetadataFactory();
    }

    public function testCreateInputMetadataWithInput()
    {
        $input = $this->factory->createInputMetadata([new TestController(), 'testWithInput']);
        $this->assertEquals(new Input('json', ['foo'], ['groups' => ['foo']]), $input);

        $input = $this->factory->createInputMetadata((new TestController())->testWithInput(...));

        $this->assertEquals(new Input('json', ['foo'], ['groups' => ['foo']]), $input);

        $input = $this->factory->createInputMetadata(new TestController());
        $this->assertEquals(new Input('json', ['bar'], ['groups' => ['bar']]), $input);
    }

    public function testCreateInputMetadataWithoutInput()
    {
        $input = $this->factory->createInputMetadata([new TestController(), 'testWithoutInput']);
        $this->assertNull($input);

        $input = $this->factory->createInputMetadata(\Closure::fromCallable([(new TestController()), 'testWithoutInput']));
        $this->assertNull($input);
    }
}
