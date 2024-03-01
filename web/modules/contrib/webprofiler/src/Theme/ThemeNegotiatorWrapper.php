<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiator;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Extends the ThemeNegotiatorWrapper to collect the current theme negotiator.
 */
class ThemeNegotiatorWrapper extends ThemeNegotiator {

  /**
   * The current theme negotiator.
   *
   * @var \Drupal\Core\Theme\ThemeNegotiatorInterface
   */
  private ThemeNegotiatorInterface $negotiator;

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    foreach ($this->negotiators as $negotiator_id) {
      $negotiator = $this->classResolver->getInstanceFromDefinition($negotiator_id);
      assert($negotiator instanceof ThemeNegotiatorInterface);

      if ($negotiator->applies($route_match)) {
        $theme = $negotiator->determineActiveTheme($route_match);
        if ($theme !== NULL && $this->themeAccess->checkAccess($theme)) {
          $this->negotiator = $negotiator;

          return $theme;
        }
      }
    }

    return NULL;
  }

  /**
   * Return the current theme negotiator.
   *
   * @return \Drupal\Core\Theme\ThemeNegotiatorInterface
   *   The current theme negotiator.
   */
  public function getNegotiator(): ThemeNegotiatorInterface {
    return $this->negotiator;
  }

}
