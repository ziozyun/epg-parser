<?php

declare(strict_types = 1);

namespace App\Application\Features\Import;

use App\Application\Interfaces\XmlTvParserInterface;
use App\Application\Interfaces\Providers\GzipXmlProviderInterface;
use App\Application\Interfaces\Providers\StreamProviderInterface;
use App\Application\Handlers\ChannelBatchHandler;
use App\Application\Handlers\ProgramBatchHandler;
use App\Application\Interfaces\LoggerInterface;
use App\Application\Interfaces\TransactionManagerInterface;
use App\Application\Services\ChannelFilterBuilder;
use App\Application\Services\ProgramFilterBuilder;
use App\Domain\Aggregates\Channel;
use App\Domain\Aggregates\Program;

class ImportUseCase
{
  public function __construct(
    private ImportRequestValidator $validator,
    private StreamProviderInterface $streamProvider,
    private GzipXmlProviderInterface $gzipXmlProvider,
    private XmlTvParserInterface $parser,
    private ChannelBatchHandler $channelHandler,
    private ProgramBatchHandler $programHandler,
    private ChannelFilterBuilder $channelFilterBuilder,
    private ProgramFilterBuilder $programFilterBuilder,
    private LoggerInterface $logger,
    private TransactionManagerInterface $transactionManager
  ) {
  }

  public function execute(ImportRequest $request): ImportResponse
  {
    $validated = $this->validator->validate($request);
    if ($validated === null) {
      return new ImportResponse(0, 0, 0, 0, $this->validator->errors());
    }

    $channelFilter = $this->channelFilterBuilder->build($validated);
    $programFilter = $this->programFilterBuilder->build($validated);

    $channelsInserted = 0;
    $channelsUpdated = 0;
    $programsInserted = 0;
    $programsUpdated = 0;
    $archivePath = null;
    $error = null;

    try {
      $this->logger->info('Завантаження архіву...');
      $archivePath = $this->streamProvider->download($validated->uri());
      if (!file_exists($archivePath)) {
        $error = 'Файл не знайдено після завантаження.';
        throw new \RuntimeException($error);
      }

      $this->logger->info('Парсинг каналів...');
      $this->parser->parseChannels(
        $archivePath,
        function (Channel $channel) use ($channelFilter): void {
          if (!$channelFilter->matches($channel)) {
            return;
          }

          $this->channelHandler->handle($channel);
        }
      );

      $this->transactionManager->begin();
      $channelsResult = $this->channelHandler->flush();
      $this->transactionManager->commit();
      $channelsInserted += $channelsResult->inserted();
      $channelsUpdated += $channelsResult->updated();
      $this->logger->info("Канали: підсумок додано {$channelsInserted}, оновлено {$channelsUpdated}.");

      $this->logger->info('Парсинг програм...');
      $this->parser->parsePrograms(
        $archivePath,
        function (Program $program) use (
          $programFilter
        ): void {
          if (!$programFilter->matches($program)) {
            return;
          }

          $this->programHandler->handle($program);
        }
      );

      $this->transactionManager->begin();
      $programsResult = $this->programHandler->flush();
      $this->transactionManager->commit();
      $programsInserted += $programsResult->inserted();
      $programsUpdated += $programsResult->updated();
      $this->logger->info("Передачі: підсумок додано {$programsInserted}, оновлено {$programsUpdated}.");
    } catch (\RuntimeException $exception) {
      $error = $exception->getMessage();
      $this->transactionManager->rollback();
    } finally {
      if ($archivePath !== null && file_exists($archivePath)) {
        $this->logger->info('Видалення архіву...');
        try {
          $this->gzipXmlProvider->deleteArchive($archivePath);
        } catch (\RuntimeException $exception) {
          if ($error === null) {
            $error = $exception->getMessage();
          }
        }
      }
    }

    if ($error !== null) {
      return new ImportResponse(0, 0, 0, 0, [$error]);
    }

    $this->logger->info('Готово.');
    return new ImportResponse(
      $programsInserted,
      $programsUpdated,
      $channelsInserted,
      $channelsUpdated
    );
  }
}
