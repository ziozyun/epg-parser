<?php

declare(strict_types = 1);

namespace App\Application\Features\Import;

class ImportRequest
{
  public function __construct(
    private string $uri,
    private ?string $channels,
    private ?string $from,
    private ?string $to
  ) {
  }

  public function uri(): string
  {
    return $this->uri;
  }

  public function channels(): ?string
  {
    return $this->channels;
  }

  public function from(): ?string
  {
    return $this->from;
  }

  public function to(): ?string
  {
    return $this->to;
  }
}
