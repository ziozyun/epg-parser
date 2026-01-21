<?php

declare(strict_types = 1);

namespace App\Application\Features\Import;

use App\Domain\ValueObjects\TimeRange;

class ImportRequestValidator
{
  /** @var string[] */
  private array $errors = [];

  public function validate(ImportRequest $request): ?ImportValidatedRequest
  {
    $this->errors = [];

    $uri = trim($request->uri());
    if ($uri === '') {
      $this->errors[] = 'Потрібно вказати посилання на XML.';
    }

    if ($uri !== '' && !filter_var($uri, FILTER_VALIDATE_URL)) {
      $this->errors[] = 'Некоректний формат URL.';
    }

    $channels = $this->parseChannels($request->channels());
    $from = $this->parseDateTime($request->from(), 'from');
    $to = $this->parseDateTime($request->to(), 'to');

    if ($from !== null && $to !== null && $from > $to) {
      $this->errors[] = 'Дата початку має бути не пізніше дати завершення.';
    }

    if ($this->errors !== []) {
      return null;
    }

    $timeRange = $this->buildTimeRange($from, $to);

    return new ImportValidatedRequest($uri, $channels, $timeRange);
  }

  /**
   * @return string[]
   */
  public function errors(): array
  {
    return $this->errors;
  }

  /**
   * @return string[]|null
   */
  private function parseChannels(?string $value): ?array
  {
    if ($value === null) {
      return null;
    }

    $value = trim($value);
    if ($value === '') {
      return null;
    }

    $parts = array_map('trim', explode(',', $value));
    $parts = array_values(array_filter($parts, static fn (string $item): bool => $item !== ''));
    if ($parts === []) {
      $this->errors[] = 'Некоректний формат каналів.';
      return null;
    }

    return $parts;
  }

  /**
   * @return \DateTimeImmutable|null
   */
  private function parseDateTime(?string $value, string $name): ?\DateTimeImmutable
  {
    if ($value === null) {
      return null;
    }

    $value = trim($value);
    if ($value === '') {
      return null;
    }

    $date = \DateTimeImmutable::createFromFormat('YmdHisO', $value);

    if ($date === false) {
      $this->errors[] = "Некоректний формат дати для поля {$name}.";
      return null;
    }

    return $date;
  }

  private function buildTimeRange(?\DateTimeImmutable $from, ?\DateTimeImmutable $to): ?TimeRange
  {
    if ($from === null) {
      return null;
    }

    if ($to === null) {
      $to = new \DateTimeImmutable(TimeRange::MAX_TO, $from->getTimezone());
    }

    return new TimeRange($from, $to);
  }
}
