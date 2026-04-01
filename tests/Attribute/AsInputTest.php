<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Attribute\AsInput;
use Sfmok\RequestInput\Enum\Source;
use Sfmok\RequestInput\Metadata\SerializationMetadata;
use Sfmok\RequestInput\Metadata\ValidationMetadata;

/**
 * @internal
 */
#[CoversClass(AsInput::class)]
class AsInputTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $input = new AsInput();
        $this->assertNull($input->source);
        $this->assertNull($input->validation);
        $this->assertNull($input->serialization);
    }

    public function testCustomValues(): void
    {
        $validation = new ValidationMetadata(skip: true, statusCode: 422, groups: ['foo']);
        $serialization = new SerializationMetadata(context: ['groups' => ['foo']]);

        $input = new AsInput(
            source: Source::QueryString,
            validation: $validation,
            serialization: $serialization,
        );
        $this->assertSame(Source::QueryString, $input->source);
        $this->assertSame($validation, $input->validation);
        $this->assertSame($serialization, $input->serialization);
    }
}
