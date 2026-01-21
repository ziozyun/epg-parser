<?php

declare(strict_types = 1);

namespace App\Application\Features\Import;

use App\Domain\ValueObjects\TimeRange;

class ImportValidatedRequest
{
  private string $uri;
  /** @var string[]|null */
  private ?array $channels;
  private ?TimeRange $timeRange;

  /**
   * @param string[]|null $channels
   */
  public function __construct(
    string $uri,
    ?array $channels,
    ?TimeRange $timeRange
  ) {
    $this->uri = $uri;
    $this->channels = $channels;
    $this->timeRange = $timeRange;
  }

  public function uri(): string
  {
    return $this->uri;
  }

  /**
   * @return string[]|null
   */
  public function channels(): ?array
  {
    return $this->channels;
  }

  public function timeRange(): ?TimeRange
  {
    return $this->timeRange;
  }
}
