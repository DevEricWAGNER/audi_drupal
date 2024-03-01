<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tracer\EventDispatcher\EventDispatcherTraceableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

/**
 * Collects events data.
 */
class EventsDataCollector extends DataCollector implements LateDataCollectorInterface, HasPanelInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  /**
   * EventsDataCollector constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(
    private readonly EventDispatcherInterface $eventDispatcher,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'events';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $this->data = [
      'called_listeners' => [],
      'called_listeners_count' => 0,
      'not_called_listeners' => [],
      'not_called_listeners_count' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect() {
    if ($this->eventDispatcher instanceof EventDispatcherTraceableInterface) {
      $count_called = 0;
      $called_listeners = $this->eventDispatcher->getCalledListeners();
      foreach ($called_listeners as &$called_events) {
        foreach ($called_events as &$priority) {
          foreach ($priority as &$listener) {
            $count_called++;
            $listener['clazz'] = $this->getMethodData($listener['class'], $listener['method']);
          }
        }
      }

      $this->data['called_listeners'] = $called_listeners;
      $this->data['called_listeners_count'] = $count_called;

      $count_not_called = 0;
      $not_called_listeners = $this->eventDispatcher->getNotCalledListeners();
      foreach ($not_called_listeners as $not_called_events) {
        foreach ($not_called_events as $not_priority) {
          $count_not_called += count($not_priority);
        }
      }

      $this->data['not_called_listeners'] = $not_called_listeners;
      $this->data['not_called_listeners_count'] = $count_not_called;
    }
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Return an array of all the events that have been dispatched.
   *
   * @return array
   *   An array of all the events that have been dispatched.
   */
  public function getCalledListeners(): array {
    return $this->data['called_listeners'];
  }

  /**
   * Return an array of all the events that have not been dispatched.
   *
   * @return array
   *   An array of all the events that have not been dispatched.
   */
  public function getNotCalledListeners(): array {
    return $this->data['not_called_listeners'];
  }

  /**
   * Return the count of the events that have been dispatched.
   *
   * @return int
   *   The count of the events that have been dispatched.
   */
  public function getCalledListenersCount(): int {
    return $this->data['called_listeners_count'];
  }

  /**
   * Return the count of the events that have not been dispatched.
   *
   * @return int
   *   The count of the events that have not been dispatched.
   */
  public function getNotCalledListenersCount(): int {
    return $this->data['not_called_listeners_count'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $tabs = [
      [
        'label' => 'Called listeners',
        'content' => $this->renderListeners($this->getCalledListeners(), 'Called listeners', TRUE),
      ],
      [
        'label' => 'Not called listeners',
        'content' => $this->renderListeners($this->getNotCalledListeners(), 'Not called listeners', FALSE),
      ],
    ];

    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => $tabs,
    ];
  }

  /**
   * Render a list of listeners.
   *
   * @param array $listeners
   *   The list of listeners to render.
   * @param string $label
   *   The list's label.
   * @param bool $called
   *   TRUE if the table is for called listeners, FALSE otherwise.
   *
   * @return array
   *   The render array of the list of blocks.
   */
  private function renderListeners(array $listeners, string $label, bool $called): array {
    if (count($listeners) == 0) {
      return [
        $label => [
          '#markup' => '<p>' . $this->t('No @label listeners collected',
              ['@label' => $label]) . '</p>',
        ],
      ];
    }

    $rows = [];
    foreach ($listeners as $name => $priorities) {
      foreach ($priorities as $priority => $subscribers) {
        foreach ($subscribers as $subscriber) {
          $rows[] = [
            $name,
            [
              'data' => [
                '#type' => 'inline_template',
                '#template' => '{{ data|raw }}',
                '#context' => [
                  'data' => $this->classLink($subscriber),
                ],
              ],
              'class' => 'webprofiler__value',
            ],
            $priority,
          ];
        }
      }
    }

    return [
      $label => [
        '#theme' => 'webprofiler_dashboard_section',
        '#data' => [
          '#type' => 'table',
          '#header' => [
            $this->t('Called listeners'),
            $called ? $this->t('Class') : $this->t('Service'),
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
      ],
    ];
  }

  /**
   * Render the link to a class.
   *
   * The class can be a regular class, a service or a closure.
   *
   * @param array $subscriber
   *   Event subscriber data.
   *
   * @return array
   *   A render array of the link to the class.
   */
  private function classLink(array $subscriber): array {
    if (isset($subscriber['class'])) {
      if ($subscriber['class'] == 'Closure') {
        return [
          '#markup' => $this->t('Closure'),
        ];
      }
      else {
        return $this->renderClassLinkFromMethodData($subscriber['clazz']);
      }
    }

    return [
      '#markup' => sprintf('%s::%s', $subscriber['service'][0], $subscriber['service'][1]),
    ];
  }

}
