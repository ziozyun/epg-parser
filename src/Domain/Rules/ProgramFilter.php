<?php

declare(strict_types = 1);

namespace App\Domain\Rules;

use App\Domain\Aggregates\Program;
use App\Domain\ValueObjects\ChannelId;
use App\Domain\ValueObjects\TimeRange;

class ProgramFilter
{
  /** @var ChannelId[]|null */
  private ?array $channelIds;
  private ?TimeRange $timeRange;

  /**
   * @param ChannelId[]|null $channelIds
   */
  public function __construct(?array $channelIds, ?TimeRange $timeRange)
  {
    $this->channelIds = $channelIds;
    $this->timeRange = $timeRange;
  }

  public function matches(Program $program): bool
  {
    if ($this->channelIds !== null && $this->channelIds !== []) {
      $matched = false;
      foreach ($this->channelIds as $channelId) {
        if ($program->channelId()->equals($channelId)) {
          $matched = true;
          break;
        }
      }
      if (!$matched) {
        return false;
      }
    }

    if ($this->timeRange !== null && !$this->timeRange->overlaps($program->timeRange())) {
      return false;
    }

    return true;
  }
}
