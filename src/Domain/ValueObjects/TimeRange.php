<?php

declare(strict_types = 1);

namespace App\Domain\ValueObjects;

class TimeRange
{
  public const MAX_TO = '9999-12-31 23:59:59';

  private \DateTimeImmutable $from;
  private \DateTimeImmutable $to;

  public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to)
  {
    if ($to <= $from) {
      throw new \InvalidArgumentException('Час "до" має бути після "від".');
    }

    $this->from = $from;
    $this->to = $to;
  }

  public function from(): \DateTimeImmutable
  {
    return $this->from;
  }

  public function to(): \DateTimeImmutable
  {
    return $this->to;
  }

  public function contains(\DateTimeImmutable $time): bool
  {
    return $time >= $this->from && $time <= $this->to;
  }

  public function overlaps(TimeRange $other): bool
  {
    return $this->from <= $other->to && $this->to >= $other->from;
  }
}
