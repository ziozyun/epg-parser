<?php

declare(strict_types = 1);

namespace App\Application\Services;

use App\Application\Features\Import\ImportValidatedRequest;
use App\Domain\Rules\ProgramFilter;
use App\Domain\ValueObjects\ChannelId;

class ProgramFilterBuilder
{
  public function build(ImportValidatedRequest $validated): ProgramFilter
  {
    $channelsFilter = $validated->channels();
    $channelIds = null;
    if ($channelsFilter !== null) {
      $channelIds = array_map(static fn (string $id): ChannelId => new ChannelId($id), $channelsFilter);
    }

    return new ProgramFilter($channelIds, $validated->timeRange());
  }
}
