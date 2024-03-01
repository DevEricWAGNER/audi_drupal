<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

/**
 * Interface for DataCollector classes.
 */
interface HasPanelInterface {

  /**
   * Return the class used to render data for this data collector.
   *
   * @return array
   *   A renderable array.
   */
  public function getPanel(): array;

}
