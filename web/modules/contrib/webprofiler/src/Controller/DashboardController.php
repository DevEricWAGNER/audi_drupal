<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\webprofiler\DataCollector\HasPanelInterface;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Controller for the Webprofiler dashboard.
 */
class DashboardController extends ControllerBase {

  /**
   * The Profiler service.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private Profiler $profiler;

  /**
   * The Template manager service.
   *
   * @var \Drupal\webprofiler\Profiler\TemplateManager
   */
  private TemplateManager $templateManager;

  /**
   * DashboardController constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   *   The Profiler service.
   * @param \Drupal\webprofiler\Profiler\TemplateManager $templateManager
   *   The Template manager service.
   */
  final public function __construct(
    Profiler $profiler,
    TemplateManager $templateManager
  ) {
    $this->profiler = $profiler;
    $this->templateManager = $templateManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): DashboardController {
    return new static(
      $container->get('webprofiler.profiler'),
      $container->get('webprofiler.template_manager'),
    );
  }

  /**
   * Controller for the whole dashboard page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A Request.
   *
   * @return array
   *   A render array for webprofiler_dashboard theme.
   */
  public function dashboard(Request $request): array {
    $this->profiler->disable();

    $token = $request->get('token');

    $profile = $this->profiler->loadProfile($token);

    if ($profile == NULL) {
      return [];
    }

    $collectors = array_filter($profile->getCollectors(), function (DataCollectorInterface $el) {
      return $el instanceof HasPanelInterface;
    });

    return [
      '#theme' => 'webprofiler_dashboard',
      '#collectors' => $collectors,
      '#token' => $token,
      '#profile' => $profile,
      '#attached' => [
        'library' => [
          'webprofiler/dashboard',
        ],
      ],
    ];
  }

  /**
   * Renders a profiler panel for the given token and type.
   *
   * @param string $token
   *   The profiler token.
   * @param string $name
   *   The panel name to render.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An ajax response.
   */
  public function panel(string $token, string $name): AjaxResponse {
    $this->profiler->disable();

    if ('empty' === $token) {
      return new AjaxResponse('');
    }

    $profile = $this->profiler->loadProfile($token);

    if ($profile == NULL) {
      return new AjaxResponse('');
    }

    $collector = $profile->getCollector($name);
    if (!($collector instanceof HasPanelInterface)) {
      return new AjaxResponse('');
    }

    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '#js-webprofiler-panel',
      [
        '#theme' => 'webprofiler_dashboard_panel',
        '#name' => $name,
        '#template' => $this->templateManager->getName($profile, $name),
        '#profile' => $profile,
      ]),
    );
    $response->addCommand(new InvokeCommand('.webprofiler__collector', 'removeClass', ['active']));
    $response->addCommand(new InvokeCommand('.webprofiler__collector-' . $name, 'addClass', ['active']));

    return $response;
  }

}
