<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters\Repositories;

use App\Application\DTO\ReportRowDto;
use App\Application\Interfaces\Repositories\ReportRepositoryInterface;

class PostgresReportRepository implements ReportRepositoryInterface
{
  public function __construct(private \PDO $pdo)
  {
  }

  public function programsCountByChannel(string $lang): array
  {
    $sql = 'SELECT c.external_id AS channel_id, '
      . 'COALESCE(ct.name, c.external_id) AS channel_name, '
      . 'COUNT(p.id) AS programs_count '
      . 'FROM channels c '
      . 'JOIN programs p ON p.channel_id = c.id '
      . 'LEFT JOIN channel_translations ct '
      . 'ON ct.channel_id = c.id AND ct.lang = :lang '
      . 'GROUP BY c.external_id, ct.name '
      . 'ORDER BY programs_count DESC';

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':lang', $lang);
    $stmt->execute();

    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
      $rows[] = new ReportRowDto(
        (string) $row['channel_id'],
        (string) $row['channel_name'],
        (int) $row['programs_count']
      );
    }

    return $rows;
  }

  public function totalProgramsCount(): int
  {
    $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM programs');
    $row = $stmt->fetch();

    return $row ? (int) $row['total'] : 0;
  }
}
