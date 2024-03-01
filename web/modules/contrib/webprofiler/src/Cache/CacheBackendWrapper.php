<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Cache;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\webprofiler\DataCollector\CacheDataCollector;

/**
 * Wraps an existing cache backend to track calls to the cache backend.
 */
class CacheBackendWrapper implements CacheBackendInterface, CacheTagsInvalidatorInterface {

  /**
   * Constructs a new CacheBackendWrapper.
   *
   * @param \Drupal\webprofiler\DataCollector\CacheDataCollector $cacheDataCollector
   *   The cache data collector to inform about cache get calls.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The wrapped cache backend.
   * @param string $bin
   *   The name of the wrapped cache bin.
   */
  public function __construct(
    protected readonly CacheDataCollector $cacheDataCollector,
    protected readonly CacheBackendInterface $cacheBackend,
    protected readonly string $bin
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function get($cid, $allow_invalid = FALSE): object|bool {
    $cache = $this->cacheBackend->get($cid, $allow_invalid);

    if ($cache) {
      $cache_copy = new \stdClass();
      $cache_copy->cid = $cache->cid;
      $cache_copy->expire = $cache->expire;
      $cache_copy->tags = $cache->tags;

      $this->cacheDataCollector->registerCacheHit($this->bin, $cache_copy);
    }
    else {
      $this->cacheDataCollector->registerCacheMiss($this->bin, $cid);
    }

    return $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(&$cids, $allow_invalid = FALSE): array {
    $cids_copy = $cids;
    $cache = $this->cacheBackend->getMultiple($cids, $allow_invalid);

    foreach ($cids_copy as $cid) {
      if (in_array($cid, $cids, TRUE)) {
        $this->cacheDataCollector->registerCacheMiss($this->bin, $cid);
      }
      else {
        $cache_copy = new \stdClass();
        $cache_copy->cid = $cid;
        $cache_copy->expire = $cache[$cid]->expire;
        $cache_copy->tags = $cache[$cid]->tags;

        $this->cacheDataCollector->registerCacheHit($this->bin, $cache_copy);
      }
    }

    return $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function set($cid, $data, $expire = Cache::PERMANENT, array $tags = []) {
    $this->cacheBackend->set($cid, $data, $expire, $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $items) {
    $this->cacheBackend->setMultiple($items);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($cid) {
    $this->cacheBackend->delete($cid);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $cids) {
    $this->cacheBackend->deleteMultiple($cids);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $this->cacheBackend->deleteAll();
  }

  /**
   * {@inheritdoc}
   */
  public function invalidate($cid) {
    $this->cacheBackend->invalidate($cid);
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateMultiple(array $cids) {
    $this->cacheBackend->invalidateMultiple($cids);
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    if ($this->cacheBackend instanceof CacheTagsInvalidatorInterface) {
      $this->cacheBackend->invalidateTags($tags);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateAll() {
    $this->cacheBackend->invalidateAll();
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    $this->cacheBackend->garbageCollection();
  }

  /**
   * {@inheritdoc}
   */
  public function removeBin() {
    $this->cacheBackend->removeBin();
  }

  /**
   * Return the wrapped cache backend.
   *
   * @return \Drupal\Core\Cache\CacheBackendInterface
   *   The wrapped cache backend.
   */
  public function getWrapped(): CacheBackendInterface {
    return $this->cacheBackend;
  }

}
