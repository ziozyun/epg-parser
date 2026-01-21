<?php

declare(strict_types = 1);

namespace App\Domain\ValueObjects;

class ChannelId
{
  private string $value;

  public function __construct(string $value)
  {
    $value = trim($value);
    if ($value === '') {
      throw new \InvalidArgumentException('Ідентифікатор каналу не може бути порожнім.');
    }

    $this->value = $value;
  }

  public function value(): string
  {
    return $this->value;
  }

  public function equals(ChannelId $other): bool
  {
    return $this->value === $other->value;
  }
}
