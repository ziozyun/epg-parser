<?php

declare(strict_types = 1);

namespace App\Application\DTO;

class UpsertResultDto
{
  public function __construct(private int $inserted, private int $updated)
  {
  }

  public function inserted(): int
  {
    return $this->inserted;
  }

  public function updated(): int
  {
    return $this->updated;
  }
}
