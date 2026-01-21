<?php

declare(strict_types = 1);

namespace App\Application\Features\Report;

use App\Application\DTO\ReportRowDto;

class ReportResponse
{
  /**
   * @param ReportRowDto[] $rows
   */
  public function __construct(private int $totalPrograms, private array $rows)
  {
  }

  public function totalPrograms(): int
  {
    return $this->totalPrograms;
  }

  /**
   * @return ReportRowDto[]
   */
  public function rows(): array
  {
    return $this->rows;
  }

  public function toText(): string
  {
    $lines = [];
    foreach ($this->rows as $row) {
      $lines[] = $row->channelName() . ' (' . $row->channelId() . '): ' . $row->programsCount();
    }
    $lines[] = 'Всього передач: ' . $this->totalPrograms;

    return implode("\n", $lines);
  }
}
