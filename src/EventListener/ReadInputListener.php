<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\EventListener;

use Sfmok\RequestInput\Attribute\Input;
use Sfmok\RequestInput\Metadata\InputMetadataFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class ReadInputListener
{
    public function __construct(
        private InputMetadataFactoryInterface $inputMetadataFactory,
        private bool $enabled = true
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $request = $event->getRequest();
        $input = $this->inputMetadataFactory->createInputMetadata($event->getController());

        if (!$input instanceof Input) {
            return;
        }

        $request->attributes->set('_input', $input);
    }
}
