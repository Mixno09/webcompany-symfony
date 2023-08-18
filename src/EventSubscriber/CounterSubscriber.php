<?php

namespace App\EventSubscriber;

use App\Services\CounterHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class CounterSubscriber implements EventSubscriberInterface
{
    private CounterHelper $counterHelper;

    public function __construct(CounterHelper $counterHelper)
    {
        $this->counterHelper = $counterHelper;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (! $event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        $this->counterHelper->incrementCount($request, $response);
        $this->counterHelper->incrementTotalCount($request, $response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
