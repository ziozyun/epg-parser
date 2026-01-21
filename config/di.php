<?php

use App\Application\Interfaces\XmlTvParserInterface;
use App\Application\Interfaces\Providers\GzipXmlProviderInterface;
use App\Application\Interfaces\Providers\StreamProviderInterface;
use App\Application\Interfaces\LoggerInterface;
use App\Application\Interfaces\TransactionManagerInterface;
use App\Application\Handlers\ChannelBatchHandler;
use App\Application\Handlers\ProgramBatchHandler;
use App\Application\Interfaces\Repositories\ChannelRepositoryInterface;
use App\Application\Interfaces\Repositories\ProgramRepositoryInterface;
use App\Application\Interfaces\Repositories\ReportRepositoryInterface;
use App\Infrastructure\Adapters\XmlTvStreamParser;
use App\Infrastructure\Adapters\Loggers\StdoutLogger;
use App\Infrastructure\Adapters\Providers\GzipXmlProvider;
use App\Infrastructure\Adapters\Providers\StreamProvider;
use App\Infrastructure\Adapters\PostgresConnectionFactory;
use App\Infrastructure\Adapters\PostgresTransactionManager;
use App\Infrastructure\Adapters\Repositories\PostgresChannelRepository;
use App\Infrastructure\Adapters\Repositories\PostgresProgramRepository;
use App\Infrastructure\Adapters\Repositories\PostgresReportRepository;

use function DI\autowire;
use function DI\factory;

$batchSize = (int) (getenv('EPG_BATCH_SIZE') ?: 500);

return [
  StreamProviderInterface::class => autowire(StreamProvider::class),
  GzipXmlProviderInterface::class => autowire(GzipXmlProvider::class),
  XmlTvParserInterface::class => autowire(XmlTvStreamParser::class),
  LoggerInterface::class => autowire(StdoutLogger::class),
  TransactionManagerInterface::class => autowire(PostgresTransactionManager::class),
  ChannelBatchHandler::class => factory(function (ChannelRepositoryInterface $repository) use ($batchSize): ChannelBatchHandler {
    return new ChannelBatchHandler($repository, $batchSize);
  }),
  ProgramBatchHandler::class => factory(function (ProgramRepositoryInterface $repository) use ($batchSize): ProgramBatchHandler {
    return new ProgramBatchHandler($repository, $batchSize);
  }),
  ChannelRepositoryInterface::class => factory(function (\PDO $pdo) use ($batchSize): PostgresChannelRepository {
    return new PostgresChannelRepository($pdo, $batchSize);
  }),
  ProgramRepositoryInterface::class => factory(function (\PDO $pdo) use ($batchSize): PostgresProgramRepository {
    return new PostgresProgramRepository($pdo, $batchSize);
  }),
  ReportRepositoryInterface::class => autowire(PostgresReportRepository::class),
  \PDO::class => factory([PostgresConnectionFactory::class, 'create']),
];
