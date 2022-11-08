<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sfmok\RequestInput\Exception\UnexpectedFormatException;
use Sfmok\RequestInput\Factory\InputFactory;
use Sfmok\RequestInput\Tests\Fixtures\Input\DummyInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InputFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideData
     */
    public function testCreateFormRequest(Request $request, string $inputClass, string $format): void
    {
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $serializerProphecy = $this->prophesize(SerializerInterface::class);
        $input = new $inputClass;
        $violations = new ConstraintViolationList([]);

        switch ($format) {
            case 'json':
            case 'xml':
                $serializerProphecy
                    ->deserialize($request->getContent(), $inputClass, $format)
                    ->willReturn($input)
                    ->shouldBeCalledOnce();
                $validatorProphecy
                    ->validate($input)
                    ->willReturn($violations)
                    ->shouldBeCalledOnce();
                break;
            case 'form':
                $serializerProphecy = $this->prophesize(Serializer::class);
                $serializerProphecy
                    ->denormalize($request->request->all(), $inputClass, $format)
                    ->willReturn($input)
                    ->shouldBeCalledOnce();
                $validatorProphecy
                    ->validate($input)
                    ->willReturn($violations)
                    ->shouldBeCalledOnce();
                break;
            default:
                $this->expectException(UnexpectedFormatException::class);
                $this->expectExceptionMessage('The input format "'.$format.'" is not supported. Supported formats are : json, xml, form.');
        }

        $inputFactory = new InputFactory($serializerProphecy->reveal(), $validatorProphecy->reveal());

        $this->assertEquals($input, $inputFactory->createFromRequest($request, $inputClass, $format));
    }

    public function provideData(): iterable
    {
        yield [new Request([], [], [], [], [], [], json_encode(['foo' => 'bar'])), DummyInput::class, 'json'];
        yield [new Request([], [], [], [], [], [], '<input><foo>bar</foo></input>'), DummyInput::class, 'xml'];
        yield [new Request([], ['foo' => 'bar']), DummyInput::class, 'form'];
        yield [new Request([], [], [], [], [], [], '<javascript></javascript>'), DummyInput::class, 'js'];
        yield [new Request([], [], [], [], [], [], '<html></html>'), DummyInput::class, 'html'];
    }
}