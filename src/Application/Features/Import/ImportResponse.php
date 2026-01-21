<?php

declare(strict_types = 1);

namespace App\Application\Features\Import;

class ImportResponse
{
  /**
   * @param string[] $errors
   */
  public function __construct(
    private int $importedPrograms,
    private int $updatedPrograms,
    private int $importedChannels,
    private int $updatedChannels,
    private array $errors = []
  ) {
  }

  public function importedPrograms(): int
  {
    return $this->importedPrograms;
  }

  public function updatedPrograms(): int
  {
    return $this->updatedPrograms;
  }

  public function importedChannels(): int
  {
    return $this->importedChannels;
  }

  public function updatedChannels(): int
  {
    return $this->updatedChannels;
  }

  /**
   * @return string[]
   */
  public function errors(): array
  {
    return $this->errors;
  }

  public function isSuccess(): bool
  {
    return $this->errors === [];
  }
}
