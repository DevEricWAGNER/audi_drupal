<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Views;

use Drupal\views\ViewExecutable;

/**
 * Traceable version of ViewExecutable.
 */
class TraceableViewExecutable extends ViewExecutable {

  /**
   * Time spent rendering the view.
   *
   * @var float
   */
  protected float $renderTime = -1;

  /**
   * Gets the build time.
   *
   * @return float
   *   The build time.
   */
  public function getBuildTime(): float {
    return $this->build_time;
  }

  /**
   * Gets the execute_time.
   *
   * @return float
   *   The execute_time.
   */
  public function getExecuteTime(): float {
    return property_exists($this, 'execute_time') ? $this->execute_time : 0.0;
  }

  /**
   * Gets the render time.
   *
   * @return float
   *   The render time.
   */
  public function getRenderTime(): float {
    return $this->renderTime;
  }

  /**
   * {@inheritdoc}
   */
  public function render($display_id = NULL) {
    $start = microtime(TRUE);

    $output = parent::render($display_id);

    $this->renderTime = microtime(TRUE) - $start;

    return $output;
  }

}
