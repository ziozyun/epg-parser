<?php

declare(strict_types = 1);

namespace App\Infrastructure\Adapters;

use App\Application\Interfaces\XmlTvParserInterface;
use App\Domain\Aggregates\Channel;
use App\Domain\Aggregates\Program;
use App\Domain\ValueObjects\ChannelId;
use App\Domain\ValueObjects\LocalizedText;

class XmlTvStreamParser implements XmlTvParserInterface
{
  public function parseChannels(string $gzPath, callable $onChannel): void
  {
    $reader = $this->openReader($gzPath);

    try {
      while ($reader->read()) {
        if ($reader->nodeType !== \XMLReader::ELEMENT) {
          continue;
        }

        if ($reader->name === 'programme') {
          break;
        }

        if ($reader->name === 'channel') {
          $channel = $this->readChannel($reader);
          if ($channel !== null) {
            $onChannel($channel);
          }
        }
      }
    } finally {
      $reader->close();
    }
  }

  public function parsePrograms(string $gzPath, callable $onProgram): void
  {
    $reader = $this->openReader($gzPath);

    try {
      while ($reader->read()) {
        if ($reader->nodeType !== \XMLReader::ELEMENT) {
          continue;
        }

        if ($reader->name === 'programme') {
          $program = $this->readProgram($reader);
          if ($program !== null) {
            $onProgram($program);
          }
        }
      }
    } finally {
      $reader->close();
    }
  }

  private function openReader(string $gzPath): \XMLReader
  {
    if (!is_file($gzPath)) {
      throw new \RuntimeException('Архів не знайдено.');
    }

    $reader = new \XMLReader();
    $opened = $reader->open(
      'compress.zlib://' . $gzPath,
      null,
      LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT
    );
    if ($opened === false) {
      throw new \RuntimeException('Не вдалося відкрити XML для читання.');
    }

    return $reader;
  }

  private function readChannel(\XMLReader $reader): ?Channel
  {
    $id = (string) $reader->getAttribute('id');
    if ($id === '') {
      return null;
    }

    $names = [];
    $depth = $reader->depth;
    while ($reader->read()) {
      if ($reader->nodeType === \XMLReader::END_ELEMENT && $reader->depth === $depth && $reader->name === 'channel') {
        break;
      }
      if ($reader->nodeType !== \XMLReader::ELEMENT || $reader->name !== 'display-name') {
        continue;
      }
      $lang = (string) $reader->getAttribute('lang');
      $value = trim($reader->readString());
      if ($lang !== '' && $value !== '') {
        $names[strtolower($lang)] = $value;
      }
    }

    if ($names === []) {
      return null;
    }

    try {
      return new Channel(
        new ChannelId($id),
        new LocalizedText($names)
      );
    } catch (\InvalidArgumentException $exception) {
      return null;
    }
  }

  private function readProgram(\XMLReader $reader): ?Program
  {
    $channelId = (string) $reader->getAttribute('channel');
    $start = (string) $reader->getAttribute('start');
    $stop = (string) $reader->getAttribute('stop');
    if ($channelId === '' || $start === '' || $stop === '') {
      return null;
    }

    $externalId = null;
    $titles = [];
    $depth = $reader->depth;
    while ($reader->read()) {
      if ($reader->nodeType === \XMLReader::END_ELEMENT && $reader->depth === $depth && $reader->name === 'programme') {
        break;
      }
      if ($reader->nodeType !== \XMLReader::ELEMENT) {
        continue;
      }
      if ($reader->name === 'id') {
        $value = trim($reader->readString());
        if ($value !== '') {
          $externalId = $value;
        }
      }
      if ($reader->name === 'title') {
        $lang = (string) $reader->getAttribute('lang');
        $value = trim($reader->readString());
        if ($lang !== '' && $value !== '') {
          $titles[strtolower($lang)] = $value;
        }
      }
    }

    if ($titles === []) {
      return null;
    }

    $startAt = $this->parseXmlDate($start);
    $endAt = $this->parseXmlDate($stop);
    if ($startAt === null || $endAt === null) {
      return null;
    }

    try {
      return new Program(
        new ChannelId($channelId),
        new LocalizedText($titles),
        $startAt,
        $endAt,
        $externalId
      );
    } catch (\InvalidArgumentException $exception) {
      return null;
    }
  }

  private function parseXmlDate(string $value): ?\DateTimeImmutable
  {
    $value = trim($value);
    if ($value === '') {
      return null;
    }

    $date = \DateTimeImmutable::createFromFormat('YmdHis O', $value)
      ?: \DateTimeImmutable::createFromFormat('YmdHisO', $value);
    if ($date === false) {
      return null;
    }

    return $date;
  }
}
