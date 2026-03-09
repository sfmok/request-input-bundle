<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInput;
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

        $inputFactory = $this->createInputFactory(false);
        $this->assertEquals([$input], $inputFactory->createFromRequest($request, $input::class));
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
        $inputFactory = $this->createInputFactory(false);
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
        $inputFactory = $this->createInputFactory(false);
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
            ->with($request->getContent(), $input::class, $request->getContentTypeFormat())
            ->willReturn($input)
        ;

        $this->validator->expects(self::never())->method('validate');
        $inputFactory = $this->createInputFactory(true);
        $this->assertEquals([$input], $inputFactory->createFromRequest($request, $input::class));
    }

    #[DataProvider('provideDataRequestWithContent')]
    public function testCreateFromRequestWithInputMetadata(Request $request): void
    {
        $input = $this->getDummyInput();
        $request->attributes->set('_input', new Input(groups: ['foo'], context: ['groups' => 'foo']));
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

        $inputFactory = $this->createInputFactory(false);
        $this->assertEquals([$input], $inputFactory->createFromRequest($request, $input::class));
    }

    public static function provideDataRequestWithContent(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'application/json'])];

        yield [new Request(server: ['CONTENT_TYPE' => 'application/x-json'])];
    }

    private function createInputFactory(bool $skipValidation): InputFactoryInterface
    {
        return new InputFactory(
            $this->serializer,
            $this->validator,
            $skipValidation,
            Input::INPUT_SUPPORTED_FORMATS
        );
    }

    private function getDummyInput(): DummyInput
    {
        return new DummyInput();
    }
}
