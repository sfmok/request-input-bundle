<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Enum\Source;
use Sfmok\RequestInput\Metadata\InputMetadataResolver;
use Sfmok\RequestInput\Metadata\SerializationMetadata;
use Sfmok\RequestInput\Metadata\ValidationMetadata;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInput;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInputWithGroups;

/**
 * @internal
 */
#[CoversClass(InputMetadataResolver::class)]
class InputMetadataResolverTest extends TestCase
{
    private InputMetadataResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new InputMetadataResolver(
            new ValidationMetadata(skip: false, statusCode: 400, groups: null),
            new SerializationMetadata(),
        );
    }

    public function testResolveReturnInputWhenClassHasInputAttribute(): void
    {
        self::assertNotNull($this->resolver->resolve(DummyInput::class));
        self::assertNotNull($this->resolver->resolve(DummyInputWithGroups::class));
    }

    public function testResolveReturnsNullWhenClassHasNoInputAttribute(): void
    {
        self::assertNull($this->resolver->resolve(\stdClass::class));
    }

    public function testResolveReturnsNullForNonExistentClass(): void
    {
        self::assertNull($this->resolver->resolve('NonExistentClass'));
    }

    public function testResolveMergesGlobalsWithAttribute(): void
    {
        $resolver = new InputMetadataResolver(
            new ValidationMetadata(skip: false, statusCode: 400, groups: null),
            new SerializationMetadata(context: ['enable_max_depth' => true]),
        );

        $resolved = $resolver->resolve(DummyInputWithGroups::class);

        self::assertSame(Source::BodyPayload, $resolved->source);
        self::assertFalse($resolved->validation->skip);
        self::assertSame(400, $resolved->validation->statusCode);
        self::assertSame(['foo'], $resolved->validation->groups);
        self::assertSame(
            ['enable_max_depth' => true, 'groups' => 'foo'],
            $resolved->serialization->context
        );
    }
}
