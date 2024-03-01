<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects config data.
 */
class ConfigDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'configs';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Registers a new requested config name.
   *
   * @param string $name
   *   The name of the config.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The config object.
   */
  public function addConfig(string $name, ImmutableConfig $config): void {
    $data = $config->get();
    unset($data['_core']);

    if (!isset($this->data['configs'][$name])) {
      $this->data['configs'][$name] = [
        'count' => 1,
        'data' => $data,
      ];
    }
    else {
      $this->data['configs'][$name]['count']++;
    }
  }

  /**
   * Callback to display the config names.
   *
   * @return array
   *   The config data.
   */
  public function getConfigs(): array {
    return $this->data['configs'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $rows = [];

    foreach ($this->data['configs'] as $name => $data) {
      $rows[] = [
        $name,
        $data['count'],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ data|raw }}',
            '#context' => [
              'data' => $this->dumpData($this->cloneVar($data['data'])),
            ],
          ],
        ],
      ];
    }

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Count'),
          $this->t('Data'),
        ],
        '#rows' => $rows,
        '#attributes' => [
          'class' => [
            'webprofiler__table',
          ],
        ],
      ],
    ];
  }

}
