<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters\Providers;

use App\Application\Interfaces\Providers\StreamProviderInterface;

class StreamProvider implements StreamProviderInterface
{
  public function download(string $uri): string
  {
    $targetDir = sys_get_temp_dir();
    if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
      throw new \RuntimeException('Не вдалося створити тимчасову директорію.');
    }

    $tempPath = tempnam($targetDir, 'epg_');
    if ($tempPath === false) {
      throw new \RuntimeException('Не вдалося створити тимчасовий файл.');
    }

    $input = @fopen($uri, 'rb');
    if ($input === false) {
      throw new \RuntimeException('Не вдалося відкрити потік за вказаним посиланням.');
    }

    $output = @fopen($tempPath, 'wb');
    if ($output === false) {
      fclose($input);
      throw new \RuntimeException('Не вдалося відкрити файл для запису.');
    }

    $copied = stream_copy_to_stream($input, $output);
    fclose($input);
    fclose($output);

    if ($copied === false) {
      throw new \RuntimeException('Не вдалося завантажити файл.');
    }

    return $tempPath;
  }
}
