<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters\Loggers;

use App\Application\Interfaces\LoggerInterface;

class NullLogger implements LoggerInterface
{
  public function info(string $message): void
  {
  }
}
