<?php

declare(strict_types = 1);

namespace App\Application\Interfaces\Repositories;

interface ReportRepositoryInterface
{
  /**
   * @return \App\Application\DTO\ReportRowDto[]
   */
  public function programsCountByChannel(string $lang): array;

  public function totalProgramsCount(): int;
}
