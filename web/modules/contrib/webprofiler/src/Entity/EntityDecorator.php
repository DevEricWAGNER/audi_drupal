<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Entity;

use Drupal\webprofiler\Decorator;

/**
 * Decorator for services that manage entities.
 */
class EntityDecorator extends Decorator {

  /**
   * Entities managed by services decorated with this decorator.
   *
   * @var array
   */
  protected array $entities;

  /**
   * Return the entities managed by services decorated with this decorator.
   *
   * @return array
   *   The entities managed by services decorated with this decorator.
   */
  public function getEntities(): array {
    return $this->entities;
  }

}
