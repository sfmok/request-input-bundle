<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Metadata\InputMetadataFactory;
use Sfmok\RequestInput\Tests\Fixtures\Controller\TestController;

class InputMetadataFactoryTest extends TestCase
{
    public function testCreateInputMetadataWithInput()
    {
        $input = (new InputMetadataFactory())->createInputMetadata([new TestController(), 'testWithInput']);
        $this->assertEquals(new Input('json', ['foo'], ['groups' => ['foo']]), $input);

        $input = (new InputMetadataFactory())->createInputMetadata(TestController::class.'::testWithInput');
        $this->assertEquals(new Input('json', ['foo'], ['groups' => ['foo']]), $input);

        $input = (new InputMetadataFactory())->createInputMetadata(new TestController());
        $this->assertEquals(new Input('json', ['bar'], ['groups' => ['bar']]), $input);
    }

    public function testCreateInputMetadataWithoutInput()
    {
        $input = (new InputMetadataFactory())->createInputMetadata([new TestController(), 'testWithoutInput']);
        $this->assertNull($input);

        $input = (new InputMetadataFactory())->createInputMetadata(\Closure::fromCallable([new TestController(), 'testWithoutInput']));
        $this->assertNull($input);

        # this will be null because attribute is not supported on closure function
        $input = (new InputMetadataFactory())->createInputMetadata(\Closure::fromCallable([new TestController(), 'testWithInput']));
        $this->assertNull($input);

        $input = (new InputMetadataFactory())->createInputMetadata(TestController::class.'::testWithoutInput');
        $this->assertNull($input);
    }
}
