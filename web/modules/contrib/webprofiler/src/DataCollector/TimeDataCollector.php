<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\tracer\TracerFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

/**
 * Collects timed events data.
 */
class TimeDataCollector extends DataCollector implements LateDataCollectorInterface {

  /**
   * TimeDataCollector constructor.
   *
   * @param \Drupal\tracer\TracerFactory $tracerFactory
   *   The tracer factory.
   */
  public function __construct(private readonly TracerFactory $tracerFactory) {
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $startTime = $request->server->get('REQUEST_TIME_FLOAT');

    $this->data = [
      'start_time' => $startTime * 1000,
      'events' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'time';
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
  public function lateCollect() {
    $this->setEvents($this->tracerFactory->getEvents());
  }

  /**
   * Set collected events to data.
   *
   * @param \Symfony\Component\Stopwatch\StopwatchEvent[] $events
   *   The request events.
   */
  public function setEvents(array $events): void {
    foreach ($events as $event) {
      $event->ensureStopped();
    }

    $this->data['events'] = $events;
  }

  /**
   * Retrieve collected events from data.
   *
   * @return \Symfony\Component\Stopwatch\StopwatchEvent[]
   *   The collected events from data.
   */
  public function getEvents(): array {
    return $this->data['events'];
  }

  /**
   * Gets the request elapsed time.
   *
   * @return float
   *   The request elapsed time.
   */
  public function getDuration(): float {
    if (!isset($this->data['events']['__section__'])) {
      return 0;
    }

    $lastEvent = $this->data['events']['__section__'];

    return $lastEvent->getOrigin() + $lastEvent->getDuration() - $this->getStartTime();
  }

  /**
   * Gets the initialization time.
   *
   * This is the time spent until the beginning of the request handling.
   *
   * @return float
   *   The initialization time.
   */
  public function getInitTime(): float {
    if (!isset($this->data['events']['__section__'])) {
      return 0;
    }

    return $this->data['events']['__section__']->getOrigin() - $this->getStartTime();
  }

  /**
   * Gets the start time.
   *
   * @return float
   *   The start time.
   */
  public function getStartTime(): float {
    return $this->data['start_time'];
  }

}
