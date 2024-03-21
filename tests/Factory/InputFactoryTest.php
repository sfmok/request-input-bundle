<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Exception\DeserializationException;
use Sfmok\RequestInput\Exception\ValidationException;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Prophecy\Prophecy\ObjectProphecy;

class InputFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $validator;
    private ObjectProphecy $serializer;

    protected function setUp(): void
    {
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
    }

    /**
     * @dataProvider provideDataRequestWithContent
     */
    public function testCreateFromRequestWithContent(Request $request): void
    {
        $input = $this->getDummyInput();

        $this->serializer
            ->deserialize($request->getContent(), $input::class, $request->getContentType(), [])
            ->willReturn($input)
            ->shouldBeCalledOnce()
        ;

        $this->validator
            ->validate($input, null, ['Default'])
            ->willReturn(new ConstraintViolationList([]))
            ->shouldBeCalledOnce()
        ;

        $inputFactory = $this->createInputFactory(false);
        $this->assertEquals($input, $inputFactory->createFromRequest($request, $input::class));
    }

    /**
     * @dataProvider provideDataRequestWithFrom
     */
    public function testCreateFromRequestWithForm(Request $request): void
    {
        $input = $this->getDummyInput();
        $data = json_encode($request->request->all());

        $this->serializer
            ->deserialize($data, $input::class, Input::INPUT_JSON_FORMAT, [])
            ->willReturn($input)
            ->shouldBeCalledOnce()
        ;

        $this->validator
            ->validate($input, null, ['Default'])
            ->willReturn(new ConstraintViolationList([]))
            ->shouldBeCalledOnce()
        ;

        $inputFactory = $this->createInputFactory(false);
        $this->assertEquals($input, $inputFactory->createFromRequest($request, $input::class));
    }

    /**
     * @dataProvider provideDataUnsupportedContentType
     */
    public function testCreateFromRequestWithUnsupportedContentType(Request $request): void
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $this->expectExceptionMessageMatches(
            '/The content-type .+. is not supported. Supported MIME types are .+./'
        );

        $this->serializer->deserialize()->shouldNotBeCalled();
        $this->validator->validate()->shouldNotBeCalled();
        $inputFactory = $this->createInputFactory(false);
        $inputFactory->createFromRequest($request, DummyInput::class);
    }

    /**
     * @dataProvider provideDataEmptyContentType
     */
    public function testCreateFromRequestWithEmptyContentType(Request $request): void
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $this->expectExceptionMessage('The "Content-Type" header must exist and not empty.');

        $this->serializer->deserialize()->shouldNotBeCalled();
        $this->validator->validate()->shouldNotBeCalled();
        $inputFactory = $this->createInputFactory(false);
        $inputFactory->createFromRequest($request, DummyInput::class);
    }

    /**
     * @dataProvider provideDataRequestWithContent
     */
    public function testCreateFromRequestWithSkipValidation(Request $request): void
    {
        $input = $this->getDummyInput();

        $this->serializer
            ->deserialize($request->getContent(), $input::class, $request->getContentType(), [])
            ->willReturn($input)
            ->shouldBeCalledOnce()
        ;

        $this->validator->validate()->shouldNotBeCalled();
        $inputFactory = $this->createInputFactory(true);
        $this->assertEquals($input, $inputFactory->createFromRequest($request, $input::class));
    }

    /**
     * @dataProvider provideDataRequestWithContent
     */
    public function testCreateFromRequestWithInputMetadata(Request $request): void
    {
        $input = $this->getDummyInput();
        $request->attributes->set('_input', new Input(groups: ['foo'], context: ['groups' => 'foo']));
        $violations = new ConstraintViolationList([]);

        $this->serializer
            ->deserialize($request->getContent(), $input::class, $request->getContentType(), ['groups' => 'foo'])
            ->willReturn($input)
            ->shouldBeCalledOnce()
        ;

        $this->validator
            ->validate($input, null, ['foo'])
            ->willReturn($violations)
            ->shouldBeCalledOnce()
        ;

        $inputFactory = $this->createInputFactory(false);
        $this->assertEquals($input, $inputFactory->createFromRequest($request, $input::class));
    }

    /**
     * @dataProvider provideDataRequestWithFrom
     */
    public function testCreateFromRequestWithDeserializationException(Request $request): void
    {
        $this->expectException(DeserializationException::class);
        $data = json_encode($request->request->all());

        $this->serializer
            ->deserialize($data, DummyInput::class, 'json', [])
            ->willThrow(UnexpectedValueException::class)
            ->shouldBeCalledOnce()
        ;

        $this->validator->validate()->shouldNotBeCalled();

        $inputFactory = $this->createInputFactory(false);
        $inputFactory->createFromRequest($request, DummyInput::class);
    }

    /**
     * @dataProvider provideDataRequestWithFrom
     */
    public function testCreateFromRequestWithValidationException(Request $request): void
    {
        $this->expectException(ValidationException::class);

        $input = $this->getDummyInput();
        $data = json_encode($request->request->all());

        $this->serializer
            ->deserialize($data, $input::class, 'json', [])
            ->willReturn($input)
            ->shouldBeCalledOnce()
        ;

        $this->validator
            ->validate($input, null, ['Default'])
            ->willReturn(new ConstraintViolationList([new ConstraintViolation('foo', null, [], null, null, null)]))
            ->shouldBeCalledOnce()
        ;

        $inputFactory = $this->createInputFactory(false);
        $inputFactory->createFromRequest($request, $input::class);
    }

    public function provideDataRequestWithContent(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'application/json'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/x-json'])];
    }

    public function provideDataRequestWithFrom(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'multipart/form-data'])];
    }

    public function provideDataUnsupportedContentType(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'application/xhtml+xml'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'text/plain'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/javascript'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'text/css'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/ld+json'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/rdf+xml'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/rss+xml'])];
    }

    public function provideDataEmptyContentType(): iterable
    {
        yield [new Request()];
        yield [new Request(server: ['CONTENT_TYPE' => ''])];
    }

    private function createInputFactory(bool $skipValidation): InputFactoryInterface
    {
        return new InputFactory(
            $this->serializer->reveal(),
            $this->validator->reveal(),
            $skipValidation,
            Input::INPUT_SUPPORTED_FORMATS
        );
    }

    private function getDummyInput(): DummyInput
    {
        return new DummyInput();
    }
}