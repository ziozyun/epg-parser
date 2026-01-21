<?php

declare(strict_types = 1);

namespace App\Application\DTO;

class ReportRowDto
{
  public function __construct(
    private string $channelId,
    private string $channelName,
    private int $programsCount
  ) {
  }

  public function channelId(): string
  {
    return $this->channelId;
  }

  public function channelName(): string
  {
    return $this->channelName;
  }

  public function programsCount(): int
  {
    return $this->programsCount;
  }
}
