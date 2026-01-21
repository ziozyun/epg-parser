<?php

declare(strict_types = 1);

namespace App\Domain\Aggregates;

use App\Domain\ValueObjects\ChannelId;
use App\Domain\ValueObjects\LocalizedText;

class Channel
{
  private ChannelId $id;
  private LocalizedText $names;

  public function __construct(ChannelId $id, LocalizedText $names)
  {
    $this->id = $id;
    $this->names = $names;
  }

  public function id(): ChannelId
  {
    return $this->id;
  }

  public function name(string $lang, ?string $fallbackLang = null): string
  {
    return $this->names->valueFor($lang, $fallbackLang);
  }

  public function names(): LocalizedText
  {
    return $this->names;
  }
}
