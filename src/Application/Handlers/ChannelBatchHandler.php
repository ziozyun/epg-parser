<?php

declare(strict_types = 1);

namespace App\Application\Handlers;

use App\Application\DTO\UpsertResultDto;
use App\Application\Interfaces\Repositories\ChannelRepositoryInterface;
use App\Domain\Aggregates\Channel;

class ChannelBatchHandler
{
  /**
   * @var Channel[]
   */
  private array $channels = [];

  /**
   * @var array<string,bool>
   */
  private array $seenChannelIds = [];

  private int $inserted = 0;
  private int $updated = 0;

  public function __construct(private ChannelRepositoryInterface $repository, private int $batchSize = 500)
  {
  }

  public function handle(Channel $channel): void
  {
    $externalId = $channel->id()->value();
    if (isset($this->seenChannelIds[$externalId])) {
      return;
    }
    $this->seenChannelIds[$externalId] = true;

    $this->channels[] = $channel;
    if (count($this->channels) >= $this->batchSize) {
      $this->flushBatch();
    }
  }

  public function flush(): UpsertResultDto
  {
    $this->flushBatch();

    $result = new UpsertResultDto($this->inserted, $this->updated);
    $this->inserted = 0;
    $this->updated = 0;

    return $result;
  }

  private function flushBatch(): void
  {
    if ($this->channels === []) {
      return;
    }

    $result = $this->repository->upsertChannels($this->channels);
    $this->inserted += $result->inserted();
    $this->updated += $result->updated();
    $this->channels = [];
  }
}
