<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Exception\UnexpectedFormatException;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
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
    public function testCreateFormRequestWithContent(Request $request): void
    {
        $input = $this->getDummyInput();
        $violations = new ConstraintViolationList([]);

        $this->serializer
            ->deserialize($request->getContent(), $input::class, $request->getContentType(), [])
            ->willReturn($input)
            ->shouldBeCalledOnce()
        ;

        $this->validator
            ->validate($input, null, ['Default'])
            ->willReturn($violations)
            ->shouldBeCalledOnce()
        ;

        $inputFactory = $this->createInputFactory(false);
        $this->assertEquals($input, $inputFactory->createFromRequest($request, $input::class, $request->getContentType()));
    }

    /**
     * @dataProvider provideDataRequestWithFrom
     */
    public function testCreateFormRequestWithForm(Request $request): void
    {
        $input = $this->getDummyInput();
        $violations = new ConstraintViolationList([]);
        $data = json_encode($request->request->all());

        $this->serializer
            ->deserialize($data, $input::class, 'json', [])
            ->willReturn($input)
            ->shouldBeCalledOnce()
        ;

        $this->validator
            ->validate($input, null, ['Default'])
            ->willReturn($violations)
            ->shouldBeCalledOnce()
        ;

        $inputFactory = $this->createInputFactory(false);
        $this->assertEquals($input, $inputFactory->createFromRequest($request, $input::class, $request->getContentType()));
    }

    /**
     * @dataProvider provideDataUnsupportedFormat
     */
    public function testCreateFormRequestFromUnsupportedFormat(Request $request): void
    {
        $this->expectException(UnexpectedFormatException::class);
        $this->expectExceptionMessageMatches('/Only the formats .+ are supported. Got .+./');

        $input = $this->getDummyInput();
        $this->serializer->deserialize()->shouldNotBeCalled();
        $this->validator->validate()->shouldNotBeCalled();
        $inputFactory = $this->createInputFactory(false);
        $inputFactory->createFromRequest($request, $input::class, $request->getContentType());
    }

    /**
     * @dataProvider provideDataRequestWithContent
     */
    public function testCreateFormRequestWithSkipValidation(Request $request): void
    {
        $input = $this->getDummyInput();

        $this->serializer
            ->deserialize($request->getContent(), $input::class, $request->getContentType(), [])
            ->willReturn($input)
            ->shouldBeCalledOnce()
        ;

        $this->validator->validate()->shouldNotBeCalled();
        $inputFactory = $this->createInputFactory(true);
        $this->assertEquals($input, $inputFactory->createFromRequest($request, $input::class, $request->getContentType()));
    }

    /**
     * @dataProvider provideDataRequestWithContent
     */
    public function testCreateFormRequestWithInputMetadata(Request $request): void
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
        $this->assertEquals($input, $inputFactory->createFromRequest($request, $input::class, $request->getContentType()));
    }

    public function provideDataRequestWithContent(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'application/json'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/xml'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/x-json'])];
    }

    public function provideDataRequestWithFrom(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'multipart/form-data'])];
    }

    public function provideDataUnsupportedFormat(): iterable
    {
        yield [new Request(server: ['CONTENT_TYPE' => 'text/html'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/xhtml+xml'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'text/plain'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/javascript'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'text/css'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/ld+json'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/rdf+xml'])];
        yield [new Request(server: ['CONTENT_TYPE' => 'application/rss+xml'])];
    }

    private function createInputFactory(bool $skipValidation): InputFactoryInterface
    {
        return new InputFactory($this->serializer->reveal(), $this->validator->reveal(), $skipValidation);
    }

    private function getDummyInput(): DummyInput
    {
        return new DummyInput();
    }
}