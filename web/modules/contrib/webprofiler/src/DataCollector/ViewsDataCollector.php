<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\Views\ViewExecutableFactoryWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects views data.
 */
class ViewsDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, PanelTrait;

  /**
   * ViewsDataCollector constructor.
   *
   * @param \Drupal\webprofiler\Views\ViewExecutableFactoryWrapper $viewExecutableFactory
   *   The view executable factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   The entity manager.
   */
  public function __construct(
    private readonly ViewExecutableFactoryWrapper $viewExecutableFactory,
    private readonly EntityTypeManagerInterface $entityManager) {
    $this->data['views'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'views';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $views = $this->viewExecutableFactory->getViews();
    $storage = $this->entityManager->getStorage('view');

    foreach ($views as $view) {
      if ($view->executed) {
        $data = [
          'id' => $view->storage->id(),
          'current_display' => $view->current_display,
          'build_time' => $view->getBuildTime(),
          'execute_time' => $view->getExecuteTime(),
          'render_time' => $view->getRenderTime(),
        ];

        $entity = $storage->load($view->storage->id());
        if ($entity->hasLinkTemplate('edit-display-form')) {
          $route = $entity->toUrl('edit-display-form');
          $route->setRouteParameter('display_id', $view->current_display);
          $data['route'] = $route->toString();
        }

        $this->data['views'][] = $data;
      }
    }
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Return the number of rendered views.
   *
   * @return int
   *   The number of rendered views.
   */
  public function getViewsCount(): int {
    return count($this->data['views']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $views = $this->data['views'];

    if (count($views) == 0) {
      return [
        '#markup' => '<p>' . $this->t('No views collected') . '</p>',
      ];
    }

    $rows = [];
    foreach ($views as $view) {
      $rows[] = [
        $view['id'],
        $view['current_display'],
        $this->renderTime($view['build_time'], 's'),
        $this->renderTime($view['execute_time'], 's'),
        $this->renderTime($view['render_time'], 's'),
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '<a href="{{ route }}" target="_blank">{{ "Edit"|t }}</a>',
            '#context' => [
              'route' => $view['route'],
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
          $this->t('Display'),
          $this->t('Build time'),
          $this->t('Execute time'),
          $this->t('Render time'),
          $this->t('Action'),
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
