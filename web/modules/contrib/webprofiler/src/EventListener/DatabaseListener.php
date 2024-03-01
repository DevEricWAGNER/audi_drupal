<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\EventListener;

use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Drupal\webprofiler\DataCollector\DatabaseDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * DatabaseListener collects data for the current request.
 */
class DatabaseListener implements EventSubscriberInterface {

  /**
   * DatabaseListener constructor.
   */
  public function __construct(private readonly DatabaseDataCollector $collector) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      StatementExecutionEndEvent::class => 'onStatementExecutionEnd',
    ];
  }

  /**
   * Collects data for the current request.
   */
  public function onStatementExecutionEnd(StatementExecutionEndEvent $event): void {
    $this->collector->addStatement($event);
  }

}
