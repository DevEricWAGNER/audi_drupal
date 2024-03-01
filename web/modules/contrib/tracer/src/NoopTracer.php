<?php

declare(strict_types = 1);

namespace Drupal\tracer;

/**
 * Empty tracer implementation.
 */
class NoopTracer implements TracerInterface {

  /**
   * {@inheritdoc}
   */
  public function start(string $category, string $name, array $attributes = []): object {
    return new \stdClass();
  }

  /**
   * {@inheritdoc}
   */
  public function openSection(object $span): object {
    return new \stdClass();
  }

  /**
   * {@inheritdoc}
   */
  public function closeSection(object $span): object {
    return new \stdClass();
  }

  /**
   * {@inheritdoc}
   */
  public function stop(object $span): void {
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents(): array {
    return [];
  }

}
