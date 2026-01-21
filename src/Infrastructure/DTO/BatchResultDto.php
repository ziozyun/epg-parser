<?php

declare(strict_types = 1);

namespace App\Infrastructure\DTO;

use App\Application\DTO\UpsertResultDto;

class BatchResultDto
{
  /**
   * @param array<string,int> $idMap
   */
  public function __construct(
    private UpsertResultDto $result,
    private array $idMap
  ) {
  }

  public function result(): UpsertResultDto
  {
    return $this->result;
  }

  /**
   * @return array<string,int>
   */
  public function idMap(): array
  {
    return $this->idMap;
  }
}
