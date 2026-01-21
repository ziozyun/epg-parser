<?php

declare(strict_types = 1);

namespace App\Application\Interfaces\Repositories;

use App\Application\DTO\UpsertResultDto;
use App\Domain\Aggregates\Channel;

interface ChannelRepositoryInterface
{
  /**
   * @param Channel[] $channels
   */
  public function upsertChannels(array $channels): UpsertResultDto;
}
