<?php

declare(strict_types = 1);

namespace App\Application\Interfaces\Providers;

interface GzipXmlProviderInterface
{
  /**
   * @return resource
   */
  public function openStream(string $gzPath);

  public function deleteArchive(string $gzPath): void;
}
