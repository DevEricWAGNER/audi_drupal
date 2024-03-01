<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Twig\Extension;

use Drupal\Core\Database\Database;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extensions to render database query information.
 */
class DatabaseExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('query_type', [$this, 'queryType']),
      new TwigFunction('query_executable', [$this, 'queryExecutable']),
    ];
  }

  /**
   * Return the type of the query.
   *
   * @param string $query
   *   A SQL query.
   *
   * @return string
   *   The type of the query.
   */
  public function queryType(string $query): string {
    $parts = explode(' ', $query);
    return strtoupper($parts[0]);
  }

  /**
   * Return the executable version of the query.
   *
   * @param array $query
   *   A query array.
   *
   * @return string
   *   The executable version of the query.
   */
  public function queryExecutable(array $query): string {
    $conn = Database::getConnection();

    $quoted = [];

    if (isset($query['args'])) {
      foreach ((array) $query['args'] as $key => $val) {
        $quoted[$key] = is_null($val) ? 'NULL' : $conn->quote($val);
      }
    }

    return strtr($query['query'], $quoted);
  }

}
