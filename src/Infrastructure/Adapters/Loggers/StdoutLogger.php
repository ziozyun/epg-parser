<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters\Loggers;

use App\Application\Interfaces\LoggerInterface;

class StdoutLogger implements LoggerInterface
{
  public function info(string $message): void
  {
    echo $message . "\n";
  }
}
