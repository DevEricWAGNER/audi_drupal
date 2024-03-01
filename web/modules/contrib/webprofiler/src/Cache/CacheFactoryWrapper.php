<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Cache;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\webprofiler\DataCollector\CacheDataCollector;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Wraps a cache factory to register all calls to the cache system.
 */
class CacheFactoryWrapper implements CacheFactoryInterface, ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * All wrapped cache backends.
   *
   * @var \Drupal\webprofiler\Cache\CacheBackendWrapper[]
   */
  protected array $cacheBackends = [];

  /**
   * Creates a new CacheFactoryWrapper instance.
   *
   * @param \Drupal\Core\Cache\CacheFactoryInterface $cacheFactory
   *   The cache factory.
   * @param \Drupal\webprofiler\DataCollector\CacheDataCollector $cacheDataCollector
   *   The cache data collector.
   */
  public function __construct(
    protected readonly CacheFactoryInterface $cacheFactory,
    protected readonly CacheDataCollector $cacheDataCollector
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function get($bin): CacheBackendInterface {
    if (!isset($this->cacheBackends[$bin])) {
      $cache_backend = $this->cacheFactory->get($bin);
      $this->cacheBackends[$bin] = new CacheBackendWrapper($this->cacheDataCollector, $cache_backend, $bin);
    }

    return $this->cacheBackends[$bin];
  }

}
