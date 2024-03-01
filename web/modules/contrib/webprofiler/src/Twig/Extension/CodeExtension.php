<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Twig\Extension;

use Drupal\webprofiler\Debug\FileLinkFormatter;
use Drupal\webprofiler\DumpTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig extension relate to PHP code and used by Webprofiler.
 */
class CodeExtension extends AbstractExtension {

  use DumpTrait;

  /**
   * Formats debug file links.
   *
   * @var \Drupal\webprofiler\Debug\FileLinkFormatter
   */
  private FileLinkFormatter $fileLinkFormat;

  /**
   * CodeExtension constructor.
   *
   * @param \Drupal\webprofiler\Debug\FileLinkFormatter $fileLinkFormat
   *   Formats debug file links.
   */
  public function __construct(FileLinkFormatter $fileLinkFormat) {
    $this->fileLinkFormat = $fileLinkFormat;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('abbr_class', [$this, 'abbrClass'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
      new TwigFilter('file_link', [$this, 'getFileLink']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('wp_dump', [$this, 'dump']),
    ];
  }

  /**
   * Return the short version of a class name.
   *
   * @param string $class
   *   A class name.
   *
   * @return string
   *   The short version of a class name.
   */
  public function abbrClass(string $class): string {
    $parts = explode('\\', $class);
    $short = array_pop($parts);

    return sprintf('<abbr title="%s">%s</abbr>', $class, $short);
  }

  /**
   * Returns a link to a source file.
   *
   * @param string $file
   *   File path.
   * @param int $line
   *   LIne number inside the file.
   *
   * @return string
   *   A link to a source file, or FALSE if the link cannot be created.
   */
  public function getFileLink(string $file, int $line): string {
    return $this->fileLinkFormat->format($file, $line);
  }

  /**
   * Dump a value.
   *
   * @param mixed $value
   *   The value to dump.
   *
   * @return string
   *   The dumped value.
   */
  public function dump(mixed $value): string {
    try {
      return $this->dumpData($this->cloneVar($value));
    }
    catch (\ErrorException $e) {
      return '';
    }
  }

}
