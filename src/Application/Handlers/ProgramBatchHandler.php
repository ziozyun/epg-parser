<?php

declare(strict_types = 1);

namespace App\Application\Handlers;

use App\Application\DTO\UpsertResultDto;
use App\Application\Interfaces\Repositories\ProgramRepositoryInterface;
use App\Domain\Aggregates\Program;

class ProgramBatchHandler
{
  /**
   * @var Program[]
   */
  private array $programs = [];

  /**
   * @var array<string,bool>
   */
  private array $seenProgramIds = [];

  private int $inserted = 0;
  private int $updated = 0;

  public function __construct(private ProgramRepositoryInterface $repository, private int $batchSize = 500)
  {
  }

  public function handle(Program $program): void
  {
    $externalId = $this->programExternalId($program);
    if (isset($this->seenProgramIds[$externalId])) {
      return;
    }
    $this->seenProgramIds[$externalId] = true;

    $this->programs[] = $program;
    if (count($this->programs) >= $this->batchSize) {
      $this->flushBatch();
    }
  }

  public function flush(): UpsertResultDto
  {
    $this->flushBatch();

    $result = new UpsertResultDto($this->inserted, $this->updated);
    $this->inserted = 0;
    $this->updated = 0;

    return $result;
  }

  private function flushBatch(): void
  {
    if ($this->programs === []) {
      return;
    }

    $result = $this->repository->upsertPrograms($this->programs);
    $this->inserted += $result->inserted();
    $this->updated += $result->updated();
    $this->programs = [];
  }

  private function programExternalId(Program $program): string
  {
    return $program->externalId()
      ?? $program->channelId()->value() . ':' . $program->startAt()->format('YmdHisO');
  }
}
