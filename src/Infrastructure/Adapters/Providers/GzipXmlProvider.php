<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters\Providers;

use App\Application\Interfaces\Providers\GzipXmlProviderInterface;

class GzipXmlProvider implements GzipXmlProviderInterface
{
  public function openStream(string $gzPath)
  {
    if (!is_file($gzPath)) {
      throw new \RuntimeException('Архів не знайдено.');
    }

    $input = @gzopen($gzPath, 'rb');
    if ($input === false) {
      throw new \RuntimeException('Не вдалося відкрити gzip архів.');
    }

    return $input;
  }

  public function deleteArchive(string $gzPath): void
  {
    if (!@unlink($gzPath)) {
      throw new \RuntimeException('Не вдалося видалити архів після розпакування.');
    }
  }
}
