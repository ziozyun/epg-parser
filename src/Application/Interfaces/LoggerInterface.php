<?php

declare(strict_types = 1);

namespace App\Application\Interfaces;

interface LoggerInterface
{
  public function info(string $message): void;
}
