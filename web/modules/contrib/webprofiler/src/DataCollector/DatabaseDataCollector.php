<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects database data.
 */
class DatabaseDataCollector extends DataCollector implements HasPanelInterface {

  /**
   * DatabaseDataCollector constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    private readonly Connection $database,
    public ConfigFactoryInterface $configFactory,
  ) {
    $this->data['queries'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $options = $this->database->getConnectionOptions();

    // Remove password for security.
    unset($options['password']);

    $this->data['database'] = $options;
  }

  /**
   * Add a statement to the list of executed queries.
   *
   * @param \Drupal\Core\Database\Event\StatementExecutionEndEvent $event
   *   The statement execution end event.
   */
  public function addStatement(StatementExecutionEndEvent $event): void {
    $this->data['queries'][] = [
      'query' => $event->queryString,
      'args' => $event->args,
      'database' => $event->key,
      'target' => $event->target,
      'caller' =>
        [
          'class' => $event->caller['class'],
          'function' => $event->caller['function'],
        ],
      'time' => $event->getElapsedTime(),
      'start' => $event->startTime,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'database';
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    // Panel is implemented in the template.
    return [];
  }

  /**
   * Return the database info.
   *
   * @return array
   *   The database info.
   */
  public function getDatabase(): array {
    return $this->data['database'];
  }

  /**
   * Return the number of execute queries.
   *
   * @return int
   *   The number of execute queries.
   */
  public function getQueryCount(): int {
    return count($this->data['queries']);
  }

  /**
   * Return a list of execute queries.
   *
   * Queries are sorted by the value of query_sort config option.
   *
   * @return array
   *   A list of execute queries.
   */
  public function getQueries(): array {
    $query_sort = $this
      ->configFactory
      ->get('webprofiler.settings')
      ->get('query_sort') ?? '';

    $queries = $this->data['queries'];
    if ('duration' === $query_sort) {
      usort($queries, function (array $a, array $b): int {
        return $a['time'] <=> $b['time'];
      });
    }

    return $queries;
  }

  /**
   * Returns the total execution time.
   *
   * @return float
   *   The total execution time.
   */
  public function getTime(): float {
    $time = 0;

    foreach ($this->data['queries'] as $query) {
      $time += $query['time'];
    }

    return $time;
  }

  /**
   * Returns the configured query highlight threshold.
   *
   * @return int
   *   The configured query highlight threshold.
   */
  public function getQueryHighlightThreshold(): int {
    return $this->configFactory->get('webprofiler.settings')->get('query_highlight');
  }

}
