<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Views;

use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\ViewEntityInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewExecutableFactory;
use Drupal\views\ViewsData;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Extends the ViewExecutableFactory to add the ability to trace the views.
 */
class ViewExecutableFactoryWrapper extends ViewExecutableFactory {

  /**
   * The list of views that have been executed.
   *
   * @var \Drupal\webprofiler\Views\TraceableViewExecutable[]
   */
  private array $views;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountInterface $user, RequestStack $request_stack, ViewsData $views_data, RouteProviderInterface $route_provider) {
    parent::__construct($user, $request_stack, $views_data, $route_provider);

    $this->views = [];
  }

  /**
   * {@inheritdoc}
   */
  public function get(ViewEntityInterface $view): ViewExecutable {
    $view_executable = new TraceableViewExecutable($view, $this->user, $this->viewsData, $this->routeProvider);
    $view_executable->setRequest($this->requestStack->getCurrentRequest());
    $this->views[] = $view_executable;

    return $view_executable;
  }

  /**
   * Return the list of views that have been executed.
   *
   * @return \Drupal\webprofiler\Views\TraceableViewExecutable[]
   *   The list of views that have been executed.
   */
  public function getViews(): array {
    return $this->views;
  }

}
