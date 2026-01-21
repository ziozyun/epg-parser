<?php

declare(strict_types = 1);

namespace App\Domain\Rules;

use App\Domain\Aggregates\Channel;
use App\Domain\ValueObjects\ChannelId;

class ChannelFilter
{
  /** @var ChannelId[]|null */
  private ?array $channelIds;

  /**
   * @param ChannelId[]|null $channelIds
   */
  public function __construct(?array $channelIds)
  {
    $this->channelIds = $channelIds;
  }

  public function matches(Channel $channel): bool
  {
    if ($this->channelIds === null || $this->channelIds === []) {
      return true;
    }

    foreach ($this->channelIds as $channelId) {
      if ($channel->id()->equals($channelId)) {
        return true;
      }
    }

    return false;
  }
}
