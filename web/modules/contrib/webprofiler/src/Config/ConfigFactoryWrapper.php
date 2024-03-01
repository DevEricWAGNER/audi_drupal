<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Config;

use Drupal\Core\Config\ConfigFactory;
use Drupal\webprofiler\DataCollector\ConfigDataCollector;

/**
 * Wraps a config factory to be able to figure out all used config files.
 */
class ConfigFactoryWrapper extends ConfigFactory {

  /**
   * The data collector to store config data.
   *
   * @var \Drupal\webprofiler\DataCollector\ConfigDataCollector
   */
  private ConfigDataCollector $dataCollector;

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    $result = parent::get($name);
    $this->dataCollector->addConfig($name, $result);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $names) {
    $results = parent::loadMultiple($names);
    foreach ($results as $name => $result) {
      $this->dataCollector->addConfig($name, $result);
    }

    return $results;
  }

  /**
   * Set the data collector to store config data.
   *
   * @param \Drupal\webprofiler\DataCollector\ConfigDataCollector $dataCollector
   *   The data collector to store config data.
   */
  public function setDataCollector(ConfigDataCollector $dataCollector): void {
    $this->dataCollector = $dataCollector;
  }

}
