<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\EventListener;

use Sfmok\RequestInput\Attribute\Input;
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

        $event->getRequest()->attributes->set('_input', $inputMetadata);
    }
}
