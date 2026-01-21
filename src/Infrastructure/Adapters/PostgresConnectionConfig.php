<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters;

class PostgresConnectionConfig
{
  public function __construct(
    private string $host,
    private string $port,
    private string $name,
    private string $user,
    private string $pass
  ) {
  }

  public static function fromEnv(): self
  {
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: 'postgres';
    $user = getenv('DB_USER') ?: 'postgres';
    $pass = getenv('DB_PASSWORD') ?: '';

    return new self($host, $port, $name, $user, $pass);
  }

  public function host(): string
  {
    return $this->host;
  }

  public function port(): string
  {
    return $this->port;
  }

  public function name(): string
  {
    return $this->name;
  }

  public function user(): string
  {
    return $this->user;
  }

  public function pass(): string
  {
    return $this->pass;
  }
}
