<?php

declare(strict_types = 1);

namespace App\Infrastructure\Utilities;

class CliOptionsParser
{
  /**
   * @param array<string, mixed> $allowedOptions
   * @return array{options: array<string, mixed>, args: array<int, string>}
   */
  public function parse(array $argv, array $allowedOptions): array
  {
    $options = $allowedOptions;
    $args = [];

    foreach (array_slice($argv, 1) as $arg) {
      if (str_starts_with($arg, '--')) {
        $pair = explode('=', substr($arg, 2), 2);
        $key = $pair[0];
        $value = $pair[1] ?? null;

        if (!array_key_exists($key, $options)) {
          fwrite(STDERR, "Помилка: невідомий параметр --{$key}.\n");
          exit(1);
        }

        $options[$key] = $value;
        continue;
      }

      $args[] = $arg;
    }

    return ['options' => $options, 'args' => $args];
  }
}
