<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters\Repositories;

use App\Application\DTO\UpsertResultDto;
use App\Application\Interfaces\Repositories\ChannelRepositoryInterface;
use App\Domain\Aggregates\Channel;
use App\Infrastructure\DTO\BatchResultDto;

class PostgresChannelRepository implements ChannelRepositoryInterface
{
  public function __construct(private \PDO $pdo, private int $batchSize = 500)
  {
  }

  public function upsertChannels(array $channels): UpsertResultDto
  {
    $inserted = 0;
    $updated = 0;

    $unique = $this->uniqueChannels($channels);
    foreach (array_chunk($unique, $this->batchSize) as $batch) {
      $result = $this->upsertChannelsBatch($batch);
      $inserted += $result->result()->inserted();
      $updated += $result->result()->updated();
      $this->upsertChannelTranslationsBatch($batch, $result->idMap());
    }

    return new UpsertResultDto($inserted, $updated);
  }

  /**
   * @param Channel[] $channels
   */
  private function upsertChannelsBatch(array $channels): BatchResultDto
  {
    $values = [];
    $params = [];
    $i = 0;
    foreach ($channels as $channel) {
      $values[] = "(:external_id_{$i})";
      $params["external_id_{$i}"] = $channel->id()->value();
      $i++;
    }

    $sql = 'INSERT INTO channels (external_id) VALUES ' . implode(', ', $values)
      . ' ON CONFLICT (external_id) DO UPDATE SET external_id = EXCLUDED.external_id'
      . ' RETURNING id, external_id, (xmax = 0) AS inserted';

    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();

    $inserted = 0;
    $updated = 0;
    $idMap = [];
    foreach ($stmt->fetchAll() as $row) {
      $idMap[$row['external_id']] = (int) $row['id'];
      if ($row['inserted']) {
        $inserted++;
      } else {
        $updated++;
      }
    }

    return new BatchResultDto(new UpsertResultDto($inserted, $updated), $idMap);
  }

  /**
   * @param Channel[] $channels
   * @param array<string,int> $idMap
   */
  private function upsertChannelTranslationsBatch(array $channels, array $idMap): void
  {
    $values = [];
    $params = [];
    $i = 0;
    foreach ($channels as $channel) {
      $channelId = $idMap[$channel->id()->value()] ?? null;
      if ($channelId === null) {
        continue;
      }
      foreach ($channel->names()->all() as $lang => $name) {
        $values[] = "(:channel_id_{$i}, :lang_{$i}, :name_{$i})";
        $params["channel_id_{$i}"] = $channelId;
        $params["lang_{$i}"] = $lang;
        $params["name_{$i}"] = $name;
        $i++;
      }
    }

    if ($values === []) {
      return;
    }

    $sql = 'INSERT INTO channel_translations (channel_id, lang, name) VALUES ' . implode(', ', $values)
      . ' ON CONFLICT (channel_id, lang) DO UPDATE SET name = EXCLUDED.name';

    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();
  }

  /**
   * @param Channel[] $channels
   * @return Channel[]
   */
  private function uniqueChannels(array $channels): array
  {
    $map = [];
    foreach ($channels as $channel) {
      $map[$channel->id()->value()] = $channel;
    }

    return array_values($map);
  }
}
