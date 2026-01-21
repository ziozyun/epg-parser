<?php

declare(strict_types = 1);

namespace App\Domain\Aggregates;

use App\Domain\ValueObjects\ChannelId;
use App\Domain\ValueObjects\LocalizedText;
use App\Domain\ValueObjects\TimeRange;

class Program
{
  private ChannelId $channelId;
  private LocalizedText $titles;
  private \DateTimeImmutable $startAt;
  private \DateTimeImmutable $endAt;
  private ?string $externalId;

  public function __construct(
    ChannelId $channelId,
    LocalizedText $titles,
    \DateTimeImmutable $startAt,
    \DateTimeImmutable $endAt,
    ?string $externalId = null
  ) {
    if ($externalId !== null) {
      $externalId = trim($externalId);
      if ($externalId === '') {
        throw new \InvalidArgumentException('Зовнішній ідентифікатор не може бути порожнім.');
      }
    }
    if ($endAt <= $startAt) {
      throw new \InvalidArgumentException('Час завершення має бути після часу початку.');
    }

    $this->channelId = $channelId;
    $this->titles = $titles;
    $this->startAt = $startAt;
    $this->endAt = $endAt;
    $this->externalId = $externalId;
  }

  public function channelId(): ChannelId
  {
    return $this->channelId;
  }

  public function title(string $lang, ?string $fallbackLang = null): string
  {
    return $this->titles->valueFor($lang, $fallbackLang);
  }

  public function titles(): LocalizedText
  {
    return $this->titles;
  }

  public function startAt(): \DateTimeImmutable
  {
    return $this->startAt;
  }

  public function endAt(): \DateTimeImmutable
  {
    return $this->endAt;
  }

  public function externalId(): ?string
  {
    return $this->externalId;
  }

  public function timeRange(): TimeRange
  {
    return new TimeRange($this->startAt, $this->endAt);
  }
}
