<?php

declare(strict_types = 1);

namespace App\Application\Interfaces\Providers;

interface StreamProviderInterface
{
  public function download(string $uri): string;
}
