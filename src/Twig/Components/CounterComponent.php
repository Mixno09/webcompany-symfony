<?php

namespace App\Twig\Components;

use App\Services\CounterHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent(template: 'components/counter_component.html.twig')]
final readonly class CounterComponent
{
    private RequestStack $requestStack;
    private CounterHelper $counterHelper;

    public function __construct(RequestStack $requestStack, CounterHelper $counterHelper)
    {
        $this->requestStack = $requestStack;
        $this->counterHelper = $counterHelper;
    }

    #[ExposeInTemplate]
    public function getCount(): int
    {
        $request = $this->requestStack->getMainRequest();

        return $this->counterHelper->getCount($request);
    }

    #[ExposeInTemplate]
    public function getTotalCount(): int
    {
        $request = $this->requestStack->getMainRequest();

        return $this->counterHelper->getTotalCount($request);
    }
}
