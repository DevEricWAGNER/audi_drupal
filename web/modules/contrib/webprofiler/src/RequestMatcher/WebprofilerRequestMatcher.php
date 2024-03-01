<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\RequestMatcher;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Exclude some path to be profiled.
 */
class WebprofilerRequestMatcher implements RequestMatcherInterface {

  /**
   * An immutable config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $config;

  /**
   * WebprofilerRequestMatcher constructor.
   *
   * @param \Drupal\Core\Path\PathMatcherInterface $pathMatcher
   *   The path matcher service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   */
  public function __construct(
    protected readonly PathMatcherInterface $pathMatcher,
    ConfigFactoryInterface $config
  ) {
    $this->config = $config->get('webprofiler.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function matches(Request $request): bool {
    $path = $request->getPathInfo();

    $patterns = $this->config->get('exclude_paths');

    // Never add Webprofiler to phpinfo page.
    $patterns .= "\r\n/admin/reports/status/php";

    // Never add Webprofiler to uninstall confirm page.
    $patterns .= "\r\n/admin/modules/uninstall/*";

    return !$this->pathMatcher->matchPath($path, $patterns);
  }

}
