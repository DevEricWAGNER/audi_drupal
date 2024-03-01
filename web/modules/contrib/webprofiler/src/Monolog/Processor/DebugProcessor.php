<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Monolog\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Monolog processor that store logs for debug.
 */
class DebugProcessor implements DebugLoggerInterface, ResetInterface {

  /**
   * Collected logs.
   *
   * @var array
   */
  private array $records = [];

  /**
   * Number of log of type error.
   *
   * @var array
   */
  private array $errorCount = [];

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|null
   */
  private ?RequestStack $requestStack;

  /**
   * DebugProcessor constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack|null $requestStack
   *   The request stack.
   */
  public function __construct(RequestStack $requestStack = NULL) {
    $this->requestStack = $requestStack;
  }

  /**
   * Store the log record.
   *
   * @param array|\Monolog\LogRecord $record
   *   The log record.
   *
   * @return array|\Monolog\LogRecord
   *   The log record.
   *
   * @throws \Exception
   */
  public function __invoke(array|LogRecord $record): array|LogRecord {
    $request = $this->requestStack?->getCurrentRequest();
    $key = $request != NULL ? spl_object_id($request) : '';

    $timestamp = $timestampRfc3339 = FALSE;
    if ($record['datetime'] instanceof \DateTimeInterface) {
      $timestamp = $record['datetime']->getTimestamp();
      $timestampRfc3339 = $record['datetime']->format(\DateTimeInterface::RFC3339_EXTENDED);
    }
    elseif (FALSE !== $timestamp = strtotime($record['datetime'])) {
      $timestampRfc3339 = (new \DateTimeImmutable($record['datetime']))->format(\DateTimeInterface::RFC3339_EXTENDED);
    }

    // Convert the record to an array if it is a LogRecord.
    if ($record instanceof LogRecord) {
      $record = $record->toArray();
    }

    // Remove the exception and backtrace from the context.
    if (isset($record['context']['exception'])) {
      unset($record['context']['exception']);
    }

    if (isset($record['context']['backtrace'])) {
      unset($record['context']['backtrace']);
    }

    $this->records[$key][] = [
      'timestamp' => $timestamp,
      'timestamp_rfc3339' => $timestampRfc3339,
      'message' => $record['message'],
      'priority' => $record['level'],
      'priorityName' => $record['level_name'],
      'context' => $record['context'],
      'channel' => $record['channel'] ?? '',
    ];

    if (!isset($this->errorCount[$key])) {
      $this->errorCount[$key] = 0;
    }

    match($record['level']) {
      Level::Error, Level::Critical, Level::Alert, Level::Emergency => ++$this->errorCount[$key],
      default => NULL,
    };

    // Convert the record back to a LogRecord.
    return new LogRecord(
      $record['datetime'],
      $record['channel'],
      Level::fromValue($record['level']),
      $record['message'],
      $record['context'],
      $record['extra'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLogs(Request $request = NULL): array {
    if (NULL !== $request) {
      return $this->records[spl_object_id($request)] ?? [];
    }

    if (0 === \count($this->records)) {
      return [];
    }

    return array_merge(...array_values($this->records));
  }

  /**
   * {@inheritdoc}
   */
  public function countErrors(Request $request = NULL): int {
    if (NULL !== $request) {
      return $this->errorCount[spl_object_id($request)] ?? 0;
    }

    return array_sum($this->errorCount);
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    $this->records = [];
    $this->errorCount = [];
  }

  /**
   * Reset the error count and records.
   */
  public function reset(): void {
    $this->clear();
  }

}
