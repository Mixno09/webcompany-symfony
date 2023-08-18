<?php

declare(strict_types=1);

namespace App\Services;

use DateInterval;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class CounterHelper
{
    public function getCount(Request $request): int
    {
        $name = $this->getCountName($request);

        return $this->getValueByName($name, $request);
    }

    public function getTotalCount(Request $request): int
    {
        return $this->getValueByName('count_total', $request);
    }

    public function incrementCount(Request $request, Response $response): void
    {
        $name = $this->getCountName($request);

        $this->incrementByName($name, $request, $response);
    }

    public function incrementTotalCount(Request $request, Response $response): void
    {
        $this->incrementByName('count_total', $request, $response);
    }

    private function incrementByName(string $name, Request $request, Response $response): void
    {
        if (! $request->isMethod('GET')) {
            return;
        }

        if ($response->getStatusCode() !== 200) {
            return;
        }

        $route = $request->attributes->get('_route');
        if ($route === null || in_array($route, ['_wdt', '_profile'], true)) {
            return;
        }

        $count = $this->getValueByName($name, $request);
        $count++;

        $expiresDate = (new DateTimeImmutable())->add(new DateInterval('P30D'));
        $cookie = Cookie::create($name)->withValue((string) $count)->withExpires($expiresDate);
        $response->headers->setCookie($cookie);
    }

    private function getValueByName(string $name, Request $request): int
    {
        $count = 1;

        if ($request->cookies->has($name)) {
            $count = $request->cookies->getInt($name);
            $count = max($count, 1);
        }

        return $count;
    }

    private function getCountName(Request $request): string
    {
        $route = $request->attributes->get('_route', '');

        return "count_{$route}";
    }
}
