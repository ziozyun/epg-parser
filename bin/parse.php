<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Features\Import\ImportRequest;
use App\Application\Features\Import\ImportUseCase;
use App\Infrastructure\Utilities\CliOptionsParser;

$defaults = [
  'channels' => null,
  'from' => null,
  'to' => null,
];
$parser = new CliOptionsParser();
$parsed = $parser->parse($argv, $defaults);
$options = $parsed['options'];
$uri = $parsed['args'][0] ?? null;

if (count($parsed['args']) > 1) {
  fwrite(STDERR, "Помилка: зайвий аргумент \"{$parsed['args'][1]}\".\n");
  exit(1);
}

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../config/di.php');
$container = $builder->build();

if ($uri === null || str_starts_with($uri, '-')) {
  fwrite(STDERR, "Помилка: потрібно вказати посилання на XML.\n");
  printUsage();
  exit(1);
}

$request = new ImportRequest(
  $uri,
  $options['channels'] ?? null,
  $options['from'] ?? null,
  $options['to'] ?? null
);

/** @var ImportUseCase $useCase */
$useCase = $container->get(ImportUseCase::class);
$response = $useCase->execute($request);

if (!$response->isSuccess()) {
  foreach ($response->errors() as $error) {
    fwrite(STDERR, "Помилка: {$error}\n");
  }
  exit(1);
}

echo "Імпорт завершено успішно.\n";
echo "Передачі: додано {$response->importedPrograms()}, оновлено {$response->updatedPrograms()}.\n";
echo "Канали: додано {$response->importedChannels()}, оновлено {$response->updatedChannels()}.\n";

function printUsage(): void
{
  echo "Використання:\n";
  echo "  php bin/parse.php <uri> [--channels=1,2,3] [--from=YYYYMMDDHHMMSS+ZZZZ] [--to=YYYYMMDDHHMMSS+ZZZZ]\n";
  echo "\n";
  echo "Приклади:\n";
  echo "  php bin/parse.php \"https://example.com/xmltv.gz\" --channels=1,3,7 --from=20260118000000+0200 --to=20260119000000+0200\n";
  echo "  php bin/parse.php \"https://example.com/xmltv.gz\" --from=20260118000000+0200\n";
}
