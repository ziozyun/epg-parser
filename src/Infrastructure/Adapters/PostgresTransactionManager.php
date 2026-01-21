<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters;

use App\Application\Interfaces\TransactionManagerInterface;

class PostgresTransactionManager implements TransactionManagerInterface
{
  public function __construct(private \PDO $pdo)
  {
  }

  public function begin(): void
  {
    if (!$this->pdo->inTransaction()) {
      $this->pdo->beginTransaction();
    }
  }

  public function commit(): void
  {
    if ($this->pdo->inTransaction()) {
      $this->pdo->commit();
    }
  }

  public function rollback(): void
  {
    if ($this->pdo->inTransaction()) {
      $this->pdo->rollBack();
    }
  }
}
