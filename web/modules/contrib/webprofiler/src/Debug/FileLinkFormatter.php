<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Debug;

/**
 * Formats debug file links.
 */
class FileLinkFormatter {

  /**
   * @var string[]
   */
  private array $fileLinkFormat;

  /**
   * FileLinkFormatter constructor.
   *
   * @param string $fileLinkFormat
   *   The file link format.
   */
  public function __construct(string $fileLinkFormat) {
    $j = strpos($fileLinkFormat, '&', max(strrpos($fileLinkFormat, '%f'), strrpos($fileLinkFormat, '%l')));
    $i = $j ? $j : \strlen($fileLinkFormat);
    $this->fileLinkFormat = [substr($fileLinkFormat, 0, $i)] + preg_split('/&([^>]++)>/', substr($fileLinkFormat, $i), -1, \PREG_SPLIT_DELIM_CAPTURE);
  }

  /**
   * Format a file link.
   *
   * @return string
   *   The formatted file link.
   */
  public function format(string $file, int $line): string {
    if ($fmt = $this->getFileLinkFormat()) {
      for ($i = 1; isset($fmt[$i]); ++$i) {
        if (str_starts_with($file, $k = $fmt[$i++])) {
          $file = substr_replace($file, $fmt[$i], 0, \strlen($k));
          break;
        }
      }

      return strtr($fmt[0], ['%f' => $file, '%l' => $line]);
    }

    return '';
  }

  /**
   * @internal
   */
  public function __sleep(): array {
    $this->fileLinkFormat = $this->getFileLinkFormat();

    return ['fileLinkFormat'];
  }

  /**
   * Returns the file link format.
   *
   * @return string[]|false
   *   The file link format.
   */
  private function getFileLinkFormat(): array|false {
    if (count($this->fileLinkFormat) > 1) {
      return $this->fileLinkFormat;
    }

    return FALSE;
  }

}
