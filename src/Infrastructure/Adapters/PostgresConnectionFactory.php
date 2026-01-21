<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters;

class PostgresConnectionFactory
{
  public function create(): \PDO
  {
    $config = PostgresConnectionConfig::fromEnv();

    $dsn = "pgsql:host={$config->host()};port={$config->port()};dbname={$config->name()}";
    $pdo = new \PDO($dsn, $config->user(), $config->pass(), [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
      \PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
  }
}
