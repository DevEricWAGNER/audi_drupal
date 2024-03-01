<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

/**
 * Interface for template aware data collectors.
 */
interface TemplateAwareDataCollectorInterface extends DataCollectorInterface {

  /**
   * Returns the template for this data collector.
   *
   * @return string|null
   *   The template for this data collector.
   */
  public static function getTemplate(): ?string;

}
