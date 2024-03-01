<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tracer\DependencyInjection\TraceableContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects services data.
 */
class ServicesDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  private ContainerInterface $container;

  /**
   * ServicesDataCollector constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'services';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    if ($this->getServicesCount() > 0) {
      $tracedData = [];
      if ($this->container instanceof TraceableContainer) {
        $tracedData = $this->container->getTracedData();
      }

      foreach (array_keys($this->getServices()) as $id) {
        $this->data['services'][$id]['initialized'] = $this->container->initialized($id);
        $this->data['services'][$id]['time'] = $tracedData[$id] ?? NULL;
      }
    }
  }

  /**
   * Set services.
   *
   * @param array $services
   *   Array of services.
   */
  public function setServices(array $services): void {
    $this->data['services'] = $services;
  }

  /**
   * Returns services.
   *
   * @return array
   *   Array of services.
   */
  public function getServices(): array {
    return $this->data['services'];
  }

  /**
   * Return the number of services.
   *
   * @return int
   *   The number of services.
   */
  public function getServicesCount(): int {
    return count($this->getServices());
  }

  /**
   * Returns array of services that are initialized.
   *
   * @return array
   *   Array of services that are initialized.
   */
  public function getInitializedServices(): array {
    return array_filter($this->getServices(), function ($item) {
      return $item['initialized'];
    });
  }

  /**
   * Returns the number of services that are initialized.
   *
   * @return int
   *   The number of services that are initialized.
   */
  public function getInitializedServicesCount(): int {
    return count($this->getInitializedServices());
  }

  /**
   * Return all services but the ones from Webprofiler itself.
   *
   * @return array
   *   All services but the ones from Webprofiler itself.
   */
  public function getInitializedServicesWithoutWebprofiler(): array {
    return array_filter($this->getInitializedServices(), function ($item) {
      return !str_starts_with($item['value']['id'], 'webprofiler');
    });
  }

  /**
   * Return the number of services but the ones from Webprofiler itself.
   *
   * @return int
   *   The number of services but the ones from Webprofiler itself.
   */
  public function getInitializedServicesWithoutWebprofilerCount(): int {
    return count($this->getInitializedServicesWithoutWebprofiler());
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => [
        [
          'label' => $this->t('Services'),
          'content' => $this->renderServices($this->data['services']),
        ],
        [
          'label' => $this->t('Middlewares'),
          'content' => $this->renderMiddlewares($this->extractMiddlewares($this->data)),
        ],
      ],
    ];
  }

  /**
   * Extract middlewares from the data.
   *
   * @param array $data
   *   All the services collected.
   *
   * @return array
   *   Only services that are middlewares.
   */
  private function extractMiddlewares(array $data): array {
    $middlewares = array_filter($data['services'], function ($service) {
      return isset($service['value']['tags']['http_middleware']);
    });

    foreach ($middlewares as &$service) {
      $service['value']['handle_method'] = $this->getMethodData($service['value']['class'], 'handle');
    }

    uasort($middlewares, function ($a, $b) {
      $va = $a['value']['tags']['http_middleware'][0]['priority'];
      $vb = $b['value']['tags']['http_middleware'][0]['priority'];

      if ($va == $vb) {
        return 0;
      }
      return ($va > $vb) ? -1 : 1;
    });

    return $middlewares;
  }

  /**
   * Render tags data.
   *
   * @param array $tags
   *   A list of service's tags.
   *
   * @return string
   *   The rendered tags as a string.
   */
  private function renderTags(array $tags): string {
    return implode(', ', array_keys(array_filter($tags, function ($tag) {
      return $tag != '_provider';
    }, ARRAY_FILTER_USE_KEY)));
  }

  /**
   * Render the provider of a service.
   *
   * @param array $tags
   *   A list of service's tags.
   *
   * @return string
   *   The rendered provider as a string.
   */
  private function renderProvider(array $tags): string {
    $tags = array_filter($tags, function ($tag) {
      return $tag == '_provider';
    }, ARRAY_FILTER_USE_KEY);

    return $tags['_provider'][0]['provider'] ?? '';
  }

  /**
   * Render a table of services.
   *
   * @param array $data
   *   Services data.
   *
   * @return array
   *   A render array for the services table.
   */
  private function renderServices(array $data): array {
    $rows = [];
    foreach ($data as $service) {
      $class_link = '';
      if (isset($service['value']['file'])) {
        $class_link = $this->renderClassLink($service['value']['file'], 0, $service['value']['class']);
      }

      $rows[] = [
        $service['value']['id'],
        [
          'data' => $class_link,
        ],
        $this->renderProvider($service['value']['tags']),
        $service['initialized'] ? 'Yes' : 'No',
        $service['value']['public'] ? 'Yes' : 'No',
        $service['value']['synthetic'] ? 'Yes' : 'No',
        $this->renderTags($service['value']['tags']),
      ];
    }

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('ID'),
          $this->t('Class'),
          $this->t('Provider'),
          $this->t('Initialized'),
          $this->t('Public'),
          $this->t('Synthetic'),
          $this->t('Tags'),
        ],
        '#rows' => $rows,
        '#attributes' => [
          'class' => [
            'webprofiler__table',
          ],
        ],
        '#sticky' => TRUE,
      ],
    ];
  }

  /**
   * Render a table of middlewares.
   *
   * @param array $extractMiddlewares
   *   Middlewares data.
   *
   * @return array
   *   A render array for the middlewares table.
   */
  private function renderMiddlewares(array $extractMiddlewares): array {
    $rows = [];
    foreach ($extractMiddlewares as $middleware) {
      $class_link = '';
      if (isset($middleware['value']['handle_method'])) {
        $class_link = $this->renderClassLinkFromMethodData($middleware['value']['handle_method']);
      }

      $rows[] = [
        $middleware['value']['id'],
        [
          'data' => $class_link,
        ],
        $this->renderProvider($middleware['value']['tags']),
        $middleware['initialized'] ? 'Yes' : 'No',
        $middleware['value']['public'] ? 'Yes' : 'No',
        $middleware['value']['synthetic'] ? 'Yes' : 'No',
        $middleware['value']['tags']['http_middleware'][0]['priority'],
      ];
    }

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('ID'),
          $this->t('Class'),
          $this->t('Provider'),
          $this->t('Initialized'),
          $this->t('Public'),
          $this->t('Synthetic'),
          $this->t('Priority'),
        ],
        '#rows' => $rows,
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
