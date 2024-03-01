<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Debug;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Factory class to create FileLinkFormatter service instances.
 */
class FileLinkFormatterFactory {

  /**
   * Return a FileLinkFormatter configured with WebProfiler settings.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   *
   * @return \Drupal\webprofiler\Debug\FileLinkFormatter
   *   A FileLinkFormatter configured with WebProfiler settings.
   */
  final public static function getFileLinkFormatter(
    ConfigFactoryInterface $configFactory
  ): FileLinkFormatter {
    $settings = $configFactory->get('webprofiler.settings');
    $ide = $settings->get('ide');
    $ide_remote_path = $settings->get('ide_remote_path');
    $ide_local_path = $settings->get('ide_local_path');

    $link_format = sprintf('%s&%s>%s', $ide, $ide_remote_path, $ide_local_path);

    return new FileLinkFormatter($link_format);
  }

}
