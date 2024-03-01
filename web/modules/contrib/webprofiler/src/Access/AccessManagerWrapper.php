<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Access;

use Drupal\Component\Utility\ArgumentsResolverInterface;
use Drupal\Core\Access\AccessException;
use Drupal\Core\Access\AccessManager;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\webprofiler\DataCollector\RequestDataCollector;

/**
 * Attaches access check services to routes and runs them on request.
 */
class AccessManagerWrapper extends AccessManager {

  /**
   * The Request data collector.
   *
   * @var \Drupal\webprofiler\DataCollector\RequestDataCollector
   */
  private RequestDataCollector $dataCollector;

  /**
   * {@inheritdoc}
   */
  protected function performCheck(
    $service_id,
    ArgumentsResolverInterface $arguments_resolver
  ): AccessResultInterface {
    $callable = $this->checkProvider->loadCheck($service_id);
    $arguments = $arguments_resolver->getArguments($callable);
    $service_access = call_user_func_array($callable, $arguments);

    if (!$service_access instanceof AccessResultInterface) {
      throw new AccessException("Access error in $service_id. Access services must return an object that implements AccessResultInterface.");
    }

    $this->dataCollector->addAccessCheck($service_id, $callable);

    return $service_access;
  }

  /**
   * Set the data collector.
   *
   * @param \Drupal\webprofiler\DataCollector\RequestDataCollector $dataCollector
   *   The data collector to set.
   */
  public function setDataCollector(RequestDataCollector $dataCollector): void {
    $this->dataCollector = $dataCollector;
  }

}
