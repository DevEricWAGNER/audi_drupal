<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector as BaseRequestDataCollector;

/**
 * Collects HTTP requests data.
 *
 * @phpstan-ignore-next-line
 */
class RequestDataCollector extends BaseRequestDataCollector implements HasPanelInterface {

  use DataCollectorTrait;
  use PanelTrait;

  public const SERVICE_ID = 'service_id';

  public const CALLABLE = 'callable';

  /**
   * The Controller resolver service.
   *
   * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
   */
  private ControllerResolverInterface $controllerResolver;

  /**
   * The list of access checks applied to this request.
   *
   * @var array
   */
  private array $accessChecks;

  /**
   * RequestDataCollector constructor.
   *
   * @param \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $controllerResolver
   *   The Controller resolver service.
   */
  public function __construct(ControllerResolverInterface $controllerResolver) {
    parent::__construct();

    $this->controllerResolver = $controllerResolver;
    $this->accessChecks = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    parent::collect($request, $response);

    $this->data['big_pipe'] = $response->headers->get('X-Drupal-BigPipe-Placeholder');

    if ($controller = $this->controllerResolver->getController($request)) {
      if (is_object($controller)) {
        $this->data['controller'] = get_class($controller);
      }
      else {
        $this->data['controller'] = $this->getMethodData(
          $controller[0], $controller[1],
        ) ?? 'no controller';
      }
      $this->data['access_checks'] = $this->accessChecks;
    }

    unset($this->data['request_attributes']['_route_params']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $tabs = [];

    if ($this->data['big_pipe']) {
      $tabs[] = [
        'label' => 'Big Pipe',
        'content' => $this->renderBigPipe($this->data['big_pipe']),
      ];
    }

    $tabs[] = [
      'label' => 'Request attributes',
      'content' => $this->renderTable(
        $this->getRequestAttributes()->all()),
    ];

    if ($this->getRequestQuery()->count() > 0) {
      $tabs[] = [
        'label' => 'GET',
        'content' => $this->renderTable(
          $this->getRequestQuery()->all()),
      ];
    }

    if ($this->getRequestRequest()->count() > 0) {
      $tabs[] = [
        'label' => 'POST',
        'content' => $this->renderTable(
          $this->getRequestRequest()->all()),
      ];
    }

    if ($this->getContent() !== '') {
      $tabs[] = [
        'label' => 'Raw content',
        'content' => $this->renderContent($this->getContent()),
      ];
    }

    if ($this->getAccessChecks()->count() > 0) {
      $tabs[] = [
        'label' => 'Access check',
        'content' => $this->renderTable(
          $this->getAccessChecks()->all()),
      ];
    }

    if ($this->getRequestCookies()->count() > 0) {
      $tabs[] = [
        'label' => 'Cookies',
        'content' => $this->renderTable(
          $this->getRequestCookies()->all()),
      ];
    }

    $tabs[] = [
      'label' => 'Session Metadata',
      'content' => $this->renderTable(
        $this->getSessionMetadata()),
    ];

    $tabs[] = [
      'label' => 'Session Attributes',
      'content' => $this->renderTable(
        $this->getSessionAttributes()),
    ];

    if ($this->getRequestCookies()->count() > 0) {
      $tabs[] = [
        'label' => 'Request headers',
        'content' => $this->renderTable(
          $this->getRequestHeaders()->all()),
      ];
    }

    if ($this->getRequestCookies()->count() > 0) {
      $tabs[] = [
        'label' => 'Server Parameters',
        'content' => $this->renderTable(
          $this->getRequestServer()->all()),
      ];
    }

    if ($this->getRequestCookies()->count() > 0) {
      $tabs[] = [
        'label' => 'Response headers',
        'content' => $this->renderTable(
          $this->getResponseHeaders()->all()),
      ];
    }

    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => $tabs,
    ];
  }

  /**
   * Save an access check.
   *
   * @param string $service_id
   *   The service id of the service implementing the access check.
   * @param array $callable
   *   The callable that implement the access check.
   */
  public function addAccessCheck(
    string $service_id,
    array $callable
  ): void {
    $this->accessChecks[] = [
      self::SERVICE_ID => $service_id,
      self::CALLABLE => $this->getMethodData($callable[0], $callable[1]),
    ];
  }

  /**
   * Return the list of access checks as ParameterBag.
   *
   * @return \Symfony\Component\HttpFoundation\ParameterBag
   *   The list of access checks.
   */
  public function getAccessChecks(): ParameterBag {
    return isset($this->data['access_checks']) ? new ParameterBag($this->data['access_checks']->getValue()) : new ParameterBag();
  }

  /**
   * Return the render array with BigPipe data.
   *
   * @param string|null $big_pipe
   *   The BigPipe placeholder.
   *
   * @return array
   *   The render array with BigPipe data.
   */
  private function renderBigPipe(?string $big_pipe): array {
    if ($big_pipe == NULL) {
      return [];
    }

    $parts = explode('&', substr($big_pipe, strlen('callback=')));
    $data = urldecode($parts[0]);

    return [
      '#type' => 'inline_template',
      '#template' => '<h3>BigPipe placeholder</h3>{{ data|raw }}',
      '#context' => [
        'data' => $data,
      ],
    ];
  }

  /**
   * Render the content of a POST request.
   *
   * @param string $content
   *   The content of a POST request.
   *
   * @return array
   *   The render array of the content.
   */
  private function renderContent(string $content): array {
    return [
      '#type' => 'inline_template',
      '#template' => '<h3>{{ title }}</h3> {% if data %}{{ data|raw }}{% else %}<em>{{ "No data"|t }}</em>{% endif %}',
      '#context' => [
        'data' => $content,
      ],
    ];
  }

}
