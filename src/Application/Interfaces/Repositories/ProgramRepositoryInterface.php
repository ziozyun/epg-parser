<?php

declare(strict_types = 1);

namespace App\Application\Interfaces\Repositories;

use App\Application\DTO\UpsertResultDto;
use App\Domain\Aggregates\Program;

interface ProgramRepositoryInterface
{
  /**
   * @param Program[] $programs
   */
  public function upsertPrograms(array $programs): UpsertResultDto;
}
