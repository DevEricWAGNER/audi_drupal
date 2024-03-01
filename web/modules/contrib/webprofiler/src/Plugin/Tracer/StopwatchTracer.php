<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Plugin\Tracer;

use Drupal\tracer\TracerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Tracer that uses Symfony Stopwatch as a backend.
 *
 * @Tracer(
 *   id = "stopwatch_tracer",
 *   label = @Translation("Stopwatch Tracer"),
 *   description = @Translation("Stopwatch Tracer"),
 *   )
 */
class StopwatchTracer implements TracerInterface {

  /**
   * The Stopwatch instance.
   *
   * @var \Symfony\Component\Stopwatch\Stopwatch
   */
  private Stopwatch $tracer;

  /**
   * StopwatchTracer constructor.
   */
  public function __construct() {
    $this->tracer = new Stopwatch();
  }

  /**
   * {@inheritdoc}
   */
  public function start(string $category, string $name, array $attributes = []): object {
    return $this->tracer->start($name, $category);
  }

  /**
   * {@inheritdoc}
   */
  public function openSection(object $span): object {
    $this->tracer->openSection();

    return $span;
  }

  /**
   * {@inheritdoc}
   */
  public function closeSection(object $span): object {
    $this->tracer->stopSection('__root__');

    return $span;
  }

  /**
   * {@inheritdoc}
   */
  public function stop(object $span): void {
    try {
      $span->stop();
    }
    catch (\Exception $e) {

    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents(): array {
    return $this->tracer->getSectionEvents('__root__');
  }

}
