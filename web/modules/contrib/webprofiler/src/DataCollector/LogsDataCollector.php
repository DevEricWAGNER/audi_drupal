<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\monolog\Logger\LoggerInterfacesAdapter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * Collects logs data.
 */
class LogsDataCollector extends DataCollector implements HasPanelInterface, LateDataCollectorInterface {

  use StringTranslationTrait;

  /**
   * LogsDataCollector constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(private readonly LoggerInterface $logger) {
    $this->data['logs'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'logs';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect() {
    $logger = $this->logger;

    if (
      $logger instanceof LoggerInterfacesAdapter and
      ($adapted_logger = $logger->getAdaptedLogger()) instanceof DebugLoggerInterface
    ) {
      $this->data['logs'] = array_map(
        function ($log) {
          unset($log['context']['exception']);
          unset($log['context']['backtrace']);

          return $log;
        },
        $adapted_logger->getLogs(),
      );
    }
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Return the number of logs.
   *
   * @return int
   *   The number of logs.
   */
  public function getLogsCount(): int {
    return count($this->data['logs']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Priority'),
          $this->t('Channel'),
          $this->t('Message'),
        ],
        '#rows' => array_map(function ($log) {
          return [
            $log['priorityName'],
            $log['channel'],
            new FormattableMarkup($log['message'], $log['context']),
          ];
        }, $this->data['logs']),
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
