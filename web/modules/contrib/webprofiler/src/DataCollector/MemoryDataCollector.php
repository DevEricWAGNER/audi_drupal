<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

/**
 * Collects memory data.
 */
class MemoryDataCollector extends DataCollector implements LateDataCollectorInterface {

  use DataCollectorTrait;

  /**
   * MemoryDataCollector constructor.
   */
  public function __construct() {
    $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $this->updateMemoryUsage();
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [
      'memory' => 0,
      'memory_limit' => $this->convertToBytes(ini_get('memory_limit')),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect() {
    $this->updateMemoryUsage();
  }

  /**
   * Return the memory used to serve the request.
   *
   * @return int
   *   The memory used to serve the request.
   */
  public function getMemory(): int {
    return $this->data['memory'];
  }

  /**
   * Return the memory limit global value.
   *
   * @return int|float
   *   The memory limit global value.
   */
  public function getMemoryLimit(): int|float {
    return $this->data['memory_limit'];
  }

  /**
   * Save the memory used value.
   */
  public function updateMemoryUsage(): void {
    $this->data['memory'] = memory_get_peak_usage(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'memory';
  }

}
