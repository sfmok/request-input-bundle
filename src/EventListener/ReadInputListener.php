<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\EventListener;

use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Exception\UnexpectedFormatException;
use Sfmok\RequestInput\Metadata\InputMetadataFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class ReadInputListener
{
    public function __construct(private InputMetadataFactoryInterface $inputMetadataFactory)
    {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $inputMetadata = $this->inputMetadataFactory->createInputMetadata($event->getController());

        if (!$inputMetadata instanceof Input) {
            return;
        }

        if (!\in_array($inputMetadata->getFormat(), Input::INPUT_SUPPORTED_FORMATS)) {
            throw new UnexpectedFormatException(sprintf(
                'Only the formats [%s] are supported. Got %s.',
                implode(', ', Input::INPUT_SUPPORTED_FORMATS),
                $inputMetadata->getFormat()
            ));
        }

        $event->getRequest()->attributes->set('_input', $inputMetadata);
    }
}
