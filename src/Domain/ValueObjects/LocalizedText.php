<?php

declare(strict_types = 1);

namespace App\Domain\ValueObjects;

class LocalizedText
{
  /** @var array<string, string> */
  private array $values;

  /**
   * @param array<string, string> $values
   */
  public function __construct(array $values)
  {
    $normalized = [];
    foreach ($values as $lang => $text) {
      $lang = strtolower(trim((string) $lang));
      $text = trim((string) $text);
      if ($lang === '' || $text === '') {
        continue;
      }
      $normalized[$lang] = $text;
    }

    if ($normalized === []) {
      throw new \InvalidArgumentException('Потрібно вказати хоча б одне значення локалізованого тексту.');
    }

    $this->values = $normalized;
  }

  public function valueFor(string $lang, ?string $fallbackLang = null): string
  {
    $lang = strtolower(trim($lang));
    if ($lang !== '' && isset($this->values[$lang])) {
      return $this->values[$lang];
    }

    if ($fallbackLang !== null) {
      $fallbackLang = strtolower(trim($fallbackLang));
      if ($fallbackLang !== '' && isset($this->values[$fallbackLang])) {
        return $this->values[$fallbackLang];
      }
    }

    return reset($this->values);
  }

  /**
   * @return array<string, string>
   */
  public function all(): array
  {
    return $this->values;
  }

  public function has(string $lang): bool
  {
    $lang = strtolower(trim($lang));
    return $lang !== '' && isset($this->values[$lang]);
  }
}
