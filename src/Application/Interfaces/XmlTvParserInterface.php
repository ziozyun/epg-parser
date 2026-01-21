<?php

declare(strict_types = 1);

namespace App\Application\Interfaces;

interface XmlTvParserInterface
{
  /**
   * @param callable $onChannel function(\App\Domain\Aggregates\Channel $channel): void
   */
  public function parseChannels(string $gzPath, callable $onChannel): void;

  /**
   * @param callable $onProgram function(\App\Domain\Aggregates\Program $program): void
   */
  public function parsePrograms(string $gzPath, callable $onProgram): void;
}
