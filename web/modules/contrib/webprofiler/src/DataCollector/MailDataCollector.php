<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects data about sent mails.
 */
class MailDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  /**
   * Collected messages.
   *
   * @var array
   */
  private array $messages;

  /**
   * MailDataCollector constructor.
   */
  public function __construct() {
    $this->messages = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $this->data['mail'] = $this->messages;
  }

  /**
   * Add a message to the collector.
   *
   * @param array $message
   *   The message to add.
   * @param \Drupal\Core\Mail\MailInterface $mail
   *   The mail plugin used to send the message.
   */
  public function addMessage(array $message, MailInterface $mail): void {
    $class = get_class($mail);
    $method = $this->getMethodData($class, 'mail');

    $this->messages[] = [
      'message' => $message,
      'method' => $method,
    ];
  }

  /**
   * Returns the number of messages sent.
   *
   * @return int
   *   The number of messages sent.
   */
  public function getMailSent(): int {
    return count($this->data['mail']);
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'mail';
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
    $data = $this->data['mail'];

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Plugin'),
          $this->t('ID'),
          $this->t('To'),
          $this->t('Data'),
        ],
        '#rows' => array_map(
          function ($key) {
            return [
              [
                'data' => [
                  $this->renderClassLinkFromMethodData($key['method']),
                ],
              ],
              $key['message']['id'],
              $key['message']['to'],
              [
                'data' => [
                  '#type' => 'inline_template',
                  '#template' => '{{ data|raw }}',
                  '#context' => [
                    'data' => $this->dumpData($this->cloneVar($key['message'])),
                  ],
                ],
              ],
            ];
          },
          $data,
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

}
