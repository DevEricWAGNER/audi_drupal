<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects states data.
 */
class StateDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'state';
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Add a state to the collected data.
   *
   * @param string $key
   *   The state key.
   */
  public function addState($key): void {
    $this->data['state_get'][$key] = isset($this->data['state_get'][$key]) ? $this->data['state_get'][$key] + 1 : 1;
  }

  /**
   * Twig callback to show all requested state keys.
   *
   * @return int
   *   The number of state keys.
   */
  public function getStateKeysCount(): int {
    return count($this->data['state_get']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $data = $this->data['state_get'];

    array_walk(
      $data,
      function (&$key, $data): void {
        $key = [
          $data,
          $key,
        ];
      },
    );

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Path'),
        ],
        '#rows' => $data,
        '#attributes' => [
          'class' => [
            'webprofiler__table',
          ],
        ],
        '#sticky' => TRUE,
      ],
    ];
  }

}
