<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Metadata\InputMetadataResolver;
use Sfmok\RequestInput\Metadata\SerializationMetadata;
use Sfmok\RequestInput\Metadata\ValidationMetadata;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInput;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInputFromQuery;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInputWithGroups;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[CoversClass(InputFactory::class)]
class InputFactoryTest extends TestCase
{
    private ValidatorInterface $validator;

    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    #[DataProvider('provideDataRequestWithContent')]
    public function testCreateFromRequestWithContent(Request $request): void
    {
        $input = $this->getDummyInput();

        $this->serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with($request->getContent(), $input::class, $request->getContentTypeFormat(), [])
            ->willReturn($input)
        ;

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($input, null, ['Default'])
            ->willReturn(new ConstraintViolationList([]))
        ;

        $inputFactory = $this->createInputFactory();
        self::assertSame($input, $inputFactory->createFromRequest($request, $input::class));
    }

    #[DataProvider('provideDataUnsupportedContentType')]
    public function testCreateFromRequestWithUnsupportedContentType(Request $request): void
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $this->expectExceptionMessageMatches(
            '/The content-type .+. is not supported. Supported MIME types are .+./'
        );

        $this->serializer->expects(self::never())->method('deserialize');
        $this->validator->expects(self::never())->method('validate');
        $inputFactory = $this->createInputFactory();
        $inputFactory->createFromRequest($request, DummyInput::class);
    }

    public static function provideDataUnsupportedContentType(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'application/xhtml+xml'])];

        yield [new Request(server: ['CONTENT_TYPE' => 'text/plain'])];

        yield [new Request(server: ['CONTENT_TYPE' => 'application/javascript'])];

        yield [new Request(server: ['CONTENT_TYPE' => 'text/css'])];

        yield [new Request(server: ['CONTENT_TYPE' => 'application/ld+json'])];

        yield [new Request(server: ['CONTENT_TYPE' => 'application/rdf+xml'])];

        yield [new Request(server: ['CONTENT_TYPE' => 'application/rss+xml'])];
    }

    #[DataProvider('provideDataEmptyContentType')]
    public function testCreateFromRequestWithEmptyContentType(Request $request): void
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $this->expectExceptionMessage('The "Content-Type" header must exist and not empty.');

        $this->serializer->expects(self::never())->method('deserialize');
        $this->validator->expects(self::never())->method('validate');
        $inputFactory = $this->createInputFactory();
        $inputFactory->createFromRequest($request, DummyInput::class);
    }

    public static function provideDataEmptyContentType(): iterable
    {
        yield [new Request()];

        yield [new Request(server: ['CONTENT_TYPE' => ''])];
    }

    #[DataProvider('provideDataRequestWithContent')]
    public function testCreateFromRequestWithSkipValidation(Request $request): void
    {
        $input = $this->getDummyInput();

        $this->serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with($request->getContent(), $input::class, $request->getContentTypeFormat(), [])
            ->willReturn($input)
        ;

        $this->validator->expects(self::never())->method('validate');
        $inputFactory = $this->createInputFactory(
            new ValidationMetadata(skip: true, statusCode: 400, groups: null),
        );
        self::assertSame($input, $inputFactory->createFromRequest($request, $input::class));
    }

    #[DataProvider('provideDataRequestWithContent')]
    public function testCreateFromRequestWithInputAttributeOverrides(Request $request): void
    {
        $input = new DummyInputWithGroups();
        $violations = new ConstraintViolationList([]);

        $this->serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with($request->getContent(), $input::class, $request->getContentTypeFormat(), ['groups' => 'foo'])
            ->willReturn($input)
        ;

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($input, null, ['foo'])
            ->willReturn($violations)
        ;

        $inputFactory = $this->createInputFactory();
        self::assertSame($input, $inputFactory->createFromRequest($request, $input::class));
    }

    public function testCreateFromRequestWithQuerySource(): void
    {
        $request = Request::create('/items', 'GET', ['title' => 'hello']);
        $input = new DummyInputFromQuery();

        $this->serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with(json_encode(['title' => 'hello'], JSON_THROW_ON_ERROR), $input::class, 'json', [])
            ->willReturn($input)
        ;

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($input, null, ['Default'])
            ->willReturn(new ConstraintViolationList([]))
        ;

        $inputFactory = $this->createInputFactory();
        self::assertSame($input, $inputFactory->createFromRequest($request, $input::class));
    }

    public function testCreateFromRequestWithGlobalSerializationContext(): void
    {
        $request = new Request(server: ['CONTENT_TYPE' => 'application/json'], content: '{}');
        $input = $this->getDummyInput();

        $this->serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with('{}', $input::class, 'json', ['enable_max_depth' => true])
            ->willReturn($input)
        ;

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($input, null, ['Default'])
            ->willReturn(new ConstraintViolationList([]))
        ;

        $inputFactory = $this->createInputFactory(
            null,
            new SerializationMetadata(context: ['enable_max_depth' => true]),
        );
        self::assertSame($input, $inputFactory->createFromRequest($request, $input::class));
    }

    public static function provideDataRequestWithContent(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'application/json'])];

        yield [new Request(server: ['CONTENT_TYPE' => 'application/x-json'])];
    }

    private function createInputFactory(
        ?ValidationMetadata $globalValidation = null,
        ?SerializationMetadata $globalSerialization = null,
    ): InputFactoryInterface {
        $resolver = new InputMetadataResolver(
            $globalValidation ?? new ValidationMetadata(skip: false, statusCode: 400, groups: null),
            $globalSerialization ?? new SerializationMetadata(),
        );

        return new InputFactory(
            $this->serializer,
            $this->validator,
            $resolver,
        );
    }

    private function getDummyInput(): DummyInput
    {
        return new DummyInput();
    }
}
