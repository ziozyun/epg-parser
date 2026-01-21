<?php

declare(strict_types = 1);

namespace App\Application\Features\Report;

class ReportRequest
{
  public function __construct(private string $lang = 'uk')
  {
  }

  public function lang(): string
  {
    return $this->lang;
  }
}
