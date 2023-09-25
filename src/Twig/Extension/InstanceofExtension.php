<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class InstanceofExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest('instanceof', [$this, 'isInstanceOf']),
        ];
    }

    public function isInstanceOf(mixed $var, mixed $instance): bool
    {
        return $var instanceof $instance;
    }
}
