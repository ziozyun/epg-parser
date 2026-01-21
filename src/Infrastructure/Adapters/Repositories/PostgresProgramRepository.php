<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters\Repositories;

use App\Application\DTO\UpsertResultDto;
use App\Application\Interfaces\Repositories\ProgramRepositoryInterface;
use App\Domain\Aggregates\Program;
use App\Infrastructure\DTO\BatchResultDto;

class PostgresProgramRepository implements ProgramRepositoryInterface
{
  public function __construct(private \PDO $pdo, private int $batchSize = 500)
  {
  }

  public function upsertPrograms(array $programs): UpsertResultDto
  {
    $inserted = 0;
    $updated = 0;

    $unique = $this->uniquePrograms($programs);
    foreach (array_chunk($unique, $this->batchSize) as $batch) {
      $channelIds = $this->fetchChannelIds($batch);
      $result = $this->upsertProgramsBatch($batch, $channelIds);
      $inserted += $result->result()->inserted();
      $updated += $result->result()->updated();
      $this->upsertProgramTranslationsBatch($batch, $result->idMap());
    }

    return new UpsertResultDto($inserted, $updated);
  }

  /**
   * @param Program[] $programs
   * @return array<string,int>
   */
  private function fetchChannelIds(array $programs): array
  {
    $externalIds = [];
    foreach ($programs as $program) {
      $externalIds[$program->channelId()->value()] = true;
    }

    if ($externalIds === []) {
      return [];
    }

    $placeholders = [];
    $params = [];
    $i = 0;
    foreach (array_keys($externalIds) as $externalId) {
      $placeholders[] = ":external_id_{$i}";
      $params["external_id_{$i}"] = $externalId;
      $i++;
    }

    $sql = 'SELECT id, external_id FROM channels WHERE external_id IN (' . implode(', ', $placeholders) . ')';
    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();

    $map = [];
    foreach ($stmt->fetchAll() as $row) {
      $map[$row['external_id']] = (int) $row['id'];
    }

    return $map;
  }

  /**
   * @param Program[] $programs
   * @param array<string,int> $channelIds
   */
  private function upsertProgramsBatch(array $programs, array $channelIds): BatchResultDto
  {
    $values = [];
    $params = [];
    $i = 0;
    foreach ($programs as $program) {
      $channelId = $channelIds[$program->channelId()->value()] ?? null;
      if ($channelId === null) {
        continue;
      }

      $startAt = $program->startAt();
      $endAt = $program->endAt();

      $values[] = "(:external_id_{$i}, :channel_id_{$i}, :start_at_{$i}, :end_at_{$i})";
      $externalId = $this->programExternalId($program);
      $params["external_id_{$i}"] = $externalId;
      $params["channel_id_{$i}"] = $channelId;
      $params["start_at_{$i}"] = $startAt->format('Y-m-d H:i:sP');
      $params["end_at_{$i}"] = $endAt->format('Y-m-d H:i:sP');
      $i++;
    }

    if ($values === []) {
      return new BatchResultDto(new UpsertResultDto(0, 0), []);
    }

    $sql = 'INSERT INTO programs (external_id, channel_id, start_at, end_at) VALUES ' . implode(', ', $values)
      . ' ON CONFLICT (external_id) DO UPDATE'
      . ' SET channel_id = EXCLUDED.channel_id, start_at = EXCLUDED.start_at, end_at = EXCLUDED.end_at'
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
   * @param Program[] $programs
   * @param array<string,int> $idMap
   */
  private function upsertProgramTranslationsBatch(array $programs, array $idMap): void
  {
    $values = [];
    $params = [];
    $i = 0;
    foreach ($programs as $program) {
      $externalId = $this->programExternalId($program);
      $programId = $idMap[$externalId] ?? null;
      if ($programId === null) {
        continue;
      }
      foreach ($program->titles()->all() as $lang => $title) {
        $values[] = "(:program_id_{$i}, :lang_{$i}, :title_{$i})";
        $params["program_id_{$i}"] = $programId;
        $params["lang_{$i}"] = $lang;
        $params["title_{$i}"] = $title;
        $i++;
      }
    }

    if ($values === []) {
      return;
    }

    $sql = 'INSERT INTO program_translations (program_id, lang, title) VALUES ' . implode(', ', $values)
      . ' ON CONFLICT (program_id, lang) DO UPDATE SET title = EXCLUDED.title';

    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();
  }

  private function programExternalId(Program $program): string
  {
    return $program->externalId()
      ?? $program->channelId()->value() . ':' . $program->startAt()->format('YmdHisO');
  }

  /**
   * @param Program[] $programs
   * @return Program[]
   */
  private function uniquePrograms(array $programs): array
  {
    $map = [];
    foreach ($programs as $program) {
      $map[$this->programExternalId($program)] = $program;
    }

    return array_values($map);
  }
}
