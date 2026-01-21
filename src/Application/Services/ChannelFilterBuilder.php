<?php

declare(strict_types = 1);

namespace App\Application\Services;

use App\Application\Features\Import\ImportValidatedRequest;
use App\Domain\Rules\ChannelFilter;
use App\Domain\ValueObjects\ChannelId;

class ChannelFilterBuilder
{
  public function build(ImportValidatedRequest $validated): ChannelFilter
  {
    $channelsFilter = $validated->channels();
    $channelIds = null;
    if ($channelsFilter !== null) {
      $channelIds = array_map(static fn (string $id): ChannelId => new ChannelId($id), $channelsFilter);
    }

    return new ChannelFilter($channelIds);
  }
}
