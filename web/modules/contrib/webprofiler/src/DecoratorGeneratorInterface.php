<?php

declare(strict_types = 1);

namespace Drupal\webprofiler;

/**
 * Interface for decorator generators.
 */
interface DecoratorGeneratorInterface {

  /**
   * Generates Entity Storage decorators.
   *
   * @throws \Exception
   */
  public function generate(): void;

  /**
   * Return the list of all available decorators.
   *
   * @return array
   *   The list of all available decorators.
   */
  public function getDecorators(): array;

}
