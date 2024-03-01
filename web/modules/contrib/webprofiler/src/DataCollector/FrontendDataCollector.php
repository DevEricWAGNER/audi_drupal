<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects frontend data.
 */
class FrontendDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'frontend';
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Set navigation data.
   *
   * @param array $data
   *   The performance data.
   */
  public function setNavigationData(array $data): void {
    $this->data['performance'] = $data;
  }

  /**
   * Set Core Web Vitals data.
   *
   * @param array $data
   *   The Core Web Vitals data.
   */
  public function setCwvData(array $data): void {
    $this->data['cwv'] = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    return [
      '#theme' => 'webprofiler_dashboard_frontend',
      '#cwv' => $this->data['cwv'],
      '#performance' => $this->data['performance'],
    ];
  }

}
