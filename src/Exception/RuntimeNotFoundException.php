<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;
use Throwable;

class RuntimeNotFoundException extends RuntimeException
{
    private ?object $command;
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, ?object $command = null)
    {
        $this->command = $command;

        parent::__construct($message, $code, $previous);
    }

    public function getCommand(): ?object
    {
        return $this->command;
    }

    public function setCommand(?object $command): void
    {
        $this->command = $command;
    }
}
