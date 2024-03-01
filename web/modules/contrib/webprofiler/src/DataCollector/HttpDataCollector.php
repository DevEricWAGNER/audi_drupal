<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\Http\HttpClientMiddleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects data about http calls during request.
 */
class HttpDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, PanelTrait;

  /**
   * HttpDataCollector constructor.
   *
   * @param \Drupal\webprofiler\Http\HttpClientMiddleware $middleware
   *   The http client middleware.
   */
  public function __construct(private readonly HttpClientMiddleware $middleware) {
    $this->data['completed'] = [];
    $this->data['failed'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'http';
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
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $completed = $this->middleware->getCompletedRequests();
    $failed = $this->middleware->getFailedRequests();

    foreach ($completed as $data) {
      /** @var \GuzzleHttp\Psr7\Request $request */
      $request = $data['request'];
      /** @var \GuzzleHttp\Psr7\Response $response */
      $response = $data['response'];
      /** @var \GuzzleHttp\TransferStats $stats */
      $stats = $data['stats'];

      $uri = $request->getUri();
      $this->data['completed'][] = [
        'request' => [
          'method' => $request->getMethod(),
          'uri' => [
            'schema' => $uri->getScheme(),
            'host' => $uri->getHost(),
            'port' => $uri->getPort(),
            'path' => $uri->getPath(),
            'query' => $uri->getQuery(),
            'fragment' => $uri->getFragment(),
          ],
          'headers' => $request->getHeaders(),
          'protocol' => $request->getProtocolVersion(),
          'request_target' => $request->getRequestTarget(),
          'stats' => [
            'transferTime' => $stats->getTransferTime(),
            'handlerStats' => $stats->getHandlerStats(),
          ],
        ],
        'response' => [
          'phrase' => $response->getReasonPhrase(),
          'status' => $response->getStatusCode(),
          'headers' => $response->getHeaders(),
          'protocol' => $response->getProtocolVersion(),
        ],
      ];
    }

    foreach ($failed as $data) {
      /** @var \GuzzleHttp\Psr7\Request $request */
      $request = $data['request'];
      /** @var \GuzzleHttp\Psr7\Response|null $response */
      $response = $data['response'];

      $uri = $request->getUri();
      $failureData = [
        'request' => [
          'method' => $request->getMethod(),
          'uri' => [
            'schema' => $uri->getScheme(),
            'host' => $uri->getHost(),
            'port' => $uri->getPort(),
            'path' => $uri->getPath(),
            'query' => $uri->getQuery(),
            'fragment' => $uri->getFragment(),
          ],
          'headers' => $request->getHeaders(),
          'protocol' => $request->getProtocolVersion(),
          'request_target' => $request->getRequestTarget(),
        ],
      ];

      if ($response != NULL) {
        $failureData['response'] = [
          'phrase' => $response->getReasonPhrase(),
          'status' => $response->getStatusCode(),
          'headers' => $response->getHeaders(),
          'protocol' => $response->getProtocolVersion(),
        ];
      }

      $this->data['failed'][] = $failureData;
    }
  }

  /**
   * Returns the number of completed requests.
   *
   * @return int
   *   The number of completed requests.
   */
  public function getCompletedRequestsCount(): int {
    return count($this->getCompletedRequests());
  }

  /**
   * Returns the completed requests.
   *
   * @return array
   *   The completed requests.
   */
  public function getCompletedRequests(): array {
    return $this->data['completed'];
  }

  /**
   * The number of failed requests.
   *
   * @return int
   *   The number of failed requests.
   */
  public function getFailedRequestsCount(): int {
    return count($this->getFailedRequests());
  }

  /**
   * Returns the failed requests.
   *
   * @return array
   *   The failed requests.
   */
  public function getFailedRequests(): array {
    return $this->data['failed'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    return array_merge(
      $this->renderHttpCalls($this->getCompletedRequests(), 'Completed'),
      $this->renderHttpCalls($this->getFailedRequests(), 'Failed'),
    );
  }

  /**
   * Render a list of blocks.
   *
   * @param array $calls
   *   The list of blocks to render.
   * @param string $label
   *   The list's label.
   *
   * @return array
   *   The render array of the list of blocks.
   */
  private function renderHttpCalls(array $calls, string $label): array {
    if (count($calls) == 0) {
      return [
        $label => [
          '#markup' => '<p>' . $this->t('No @label HTTP calls collected',
              ['@label' => $label]) . '</p>',
        ],
      ];
    }

    $rows = [];
    foreach ($calls as $call) {
      $rows[] = [
        $call['request']['method'],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ data|raw }}',
            '#context' => [
              'data' => $this->dumpData($this->cloneVar($call['request']['uri'])),
            ],
          ],
        ],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ data|raw }}',
            '#context' => [
              'data' => $this->dumpData($this->cloneVar($call['request']['headers'])),
            ],
          ],
        ],
        $call['request']['protocol'],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ data|raw }}',
            '#context' => [
              'data' => $this->dumpData($this->cloneVar($call['request']['stats'])),
            ],
          ],
        ],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ data|raw }}',
            '#context' => [
              'data' => $this->dumpData($this->cloneVar($call['response'])),
            ],
          ],
        ],
      ];
    }

    return [
      $label => [
        '#theme' => 'webprofiler_dashboard_section',
        '#title' => $label,
        '#data' => [
          '#type' => 'table',
          '#header' => [
            $this->t('Method'),
            $this->t('Uri'),
            $this->t('Request headers'),
            $this->t('Request protocol'),
            $this->t('Request stats'),
            $this->t('Response'),
          ],
          '#rows' => $rows,
          '#attributes' => [
            'class' => [
              'webprofiler__table',
            ],
          ],
          '#sticky' => TRUE,
        ],
      ],
    ];
  }

}
