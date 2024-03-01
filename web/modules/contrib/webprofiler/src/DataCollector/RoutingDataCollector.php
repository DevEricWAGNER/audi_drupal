<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects routing data.
 */
class RoutingDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, PanelTrait;

  /**
   * Constructs a new RoutingDataCollector.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider.
   */
  public function __construct(private readonly RouteProviderInterface $routeProvider) {
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'routing';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $this->data['routing'] = [];
    foreach ($this->routeProvider->getAllRoutes() as $route_name => $route) {
      $this->data['routing'][] = [
        'name' => $route_name,
        'path' => $route->getPath(),
        'defaults' => $route->getDefaults(),
        'requirements' => $route->getRequirements(),
        'options' => $route->getOptions(),
      ];
    }
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Return the number of routes.
   *
   * @return int
   *   The number of routes.
   */
  public function getRoutesCount(): int {
    return count($this->routing());
  }

  /**
   * Twig callback for displaying the routes.
   */
  public function routing(): array {
    return $this->data['routing'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $data = $this->data['routing'];

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Path'),
          $this->t('Title'),
          $this->t('Controller'),
        ],
        '#rows' => array_map(
          function ($data) {
            return [
              $data['name'],
              $data['path'],
              $data['defaults']['_title'] ?? '',
              $this->renderControllerData($data['defaults']),
            ];
          }, $data,
        ),
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
   * Render the controller data.
   *
   * @param array $data
   *   The controller data.
   */
  private function renderControllerData(array $data): TranslatableMarkup|string {
    if (array_key_exists('_controller', $data)) {
      return $this->t('Controller: %controller', ['%controller' => $data['_controller']]);
    }

    if (array_key_exists('_form', $data)) {
      return $this->t('Form: %form', ['%form' => $data['_form']]);
    }

    if (array_key_exists('_entity_form', $data)) {
      return $this->t('Entity form: %entity_form', ['%entity_form' => $data['_entity_form']]);
    }

    if (array_key_exists('_entity_view', $data)) {
      return $this->t('Entity view: %entity_view', ['%entity_view' => $data['_entity_view']]);
    }

    if (array_key_exists('_entity_list', $data)) {
      return $this->t('Entity list: %entity_list', ['%entity_list' => $data['_entity_list']]);
    }

    return '';
  }

}
