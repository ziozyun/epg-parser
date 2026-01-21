<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Features\Report\ReportRequest;
use App\Application\Features\Report\ReportUseCase;
use App\Infrastructure\Utilities\CliOptionsParser;

$defaults = [
  'lang' => null,
];
$parser = new CliOptionsParser();
$parsed = $parser->parse($argv, $defaults);
$options = $parsed['options'];

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../config/di.php');
$container = $builder->build();

/** @var ReportUseCase $reportUseCase */
$reportUseCase = $container->get(ReportUseCase::class);
$lang = $options['lang'] ?? 'uk';
$report = $reportUseCase->generateReport(new ReportRequest($lang));

echo $report->toText() . "\n";
