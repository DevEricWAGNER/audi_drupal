<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects cache data.
 */
class CacheDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  const WEBPROFILER_CACHE_HIT = 'bin_cids_hit';

  const WEBPROFILER_CACHE_MISS = 'bin_cids_miss';

  /**
   * CacheDataCollector constructor.
   */
  public function __construct() {
    $this->data['total'][CacheDataCollector::WEBPROFILER_CACHE_HIT] = 0;
    $this->data['total'][CacheDataCollector::WEBPROFILER_CACHE_MISS] = 0;
    $this->data['cache'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'cache';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Registers a cache hit on a specific cache bin.
   *
   * @param string $bin
   *   The bin name.
   * @param object $cache
   *   The cache object.
   */
  public function registerCacheHit(string $bin, object $cache): void {
    $current = $this->data['cache'][$bin][$cache->cid] ?? NULL;

    if (!$current) {
      $current = [
        'cache' => [
          'cid' => $cache->cid,
          'tags' => $cache->tags,
        ],
        'count' => [
          CacheDataCollector::WEBPROFILER_CACHE_HIT => 0,
          CacheDataCollector::WEBPROFILER_CACHE_MISS => 0,
        ],
      ];

      $this->data['cache'][$bin][$cache->cid] = $current;
    }

    $this->data['cache'][$bin][$cache->cid]['count'][CacheDataCollector::WEBPROFILER_CACHE_HIT]++;
    $this->data['total'][CacheDataCollector::WEBPROFILER_CACHE_HIT]++;
  }

  /**
   * Registers a cache miss on a specific cache bin.
   *
   * @param string $bin
   *   The bin name.
   * @param string $cid
   *   The cache cid.
   */
  public function registerCacheMiss(string $bin, string $cid): void {
    $current = $this->data['cache'][$bin][$cid] ?? NULL;

    if (!$current) {
      $current = [
        'cache' => [
          'cid' => $cid,
          'tags' => [],
        ],
        'count' => [
          CacheDataCollector::WEBPROFILER_CACHE_HIT => 0,
          CacheDataCollector::WEBPROFILER_CACHE_MISS => 0,
        ],
      ];

      $this->data['cache'][$bin][$cid] = $current;
    }

    $this->data['cache'][$bin][$cid]['count'][CacheDataCollector::WEBPROFILER_CACHE_MISS]++;
    $this->data['total'][CacheDataCollector::WEBPROFILER_CACHE_MISS]++;
  }

  /**
   * Callback to return the total amount of requested cache CIDS.
   *
   * @param string $type
   *   The type of collected data.
   *
   * @return int
   *   The total amount of requested cache CIDS.
   */
  public function getCacheCidsCount(string $type): int {
    return $this->data['total'][$type];
  }

  /**
   * Callback to return the total amount of hit cache CIDS.
   *
   * @return int
   *   The total amount of hit cache CIDS.
   */
  public function getCacheHitsCount(): int {
    return $this->getCacheCidsCount(CacheDataCollector::WEBPROFILER_CACHE_HIT);
  }

  /**
   * Callback to return the total amount of miss cache CIDS.
   *
   * @return int
   *   The total amount of miss cache CIDS.
   */
  public function getCacheMissesCount(): int {
    return $this->getCacheCidsCount(CacheDataCollector::WEBPROFILER_CACHE_MISS);
  }

  /**
   * Callback to return the total amount of hit cache CIDs keyed by bin.
   *
   * @param string $type
   *   The type of collected data.
   *
   * @return array
   *   The total amount of hit cache CIDs keyed by bin.
   */
  public function cacheCids(string $type): array {
    $hits = [];
    foreach ($this->data['cache'] as $bin => $caches) {
      $hits[$bin] = 0;
      foreach ($caches as $cache) {
        $hits[$bin] += $cache['count'][$type];
      }
    }

    return $hits;
  }

  /**
   * Callback to return hit cache CIDs keyed by bin.
   *
   * @return array
   *   Hit cache CIDs keyed by bin.
   */
  public function getCacheHits(): array {
    return $this->cacheCids(CacheDataCollector::WEBPROFILER_CACHE_HIT);
  }

  /**
   * Callback to return miss cache CIDs keyed by bin.
   *
   * @return array
   *   Miss cache CIDs keyed by bin.
   */
  public function getCacheMisses(): array {
    return $this->cacheCids(CacheDataCollector::WEBPROFILER_CACHE_MISS);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $tabs = [];
    foreach (array_keys($this->data['cache']) as $bin) {
      $tabs[] = [
        'label' => $bin,
        'content' => $this->renderTable($this->data['cache'][$bin]),
      ];
    }

    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => $tabs,
    ];
  }

  /**
   * Render cache bin table.
   *
   * @param array $data
   *   The cache data for the bin.
   *
   * @return array
   *   A render array for the cache bin table.
   */
  public function renderTable(array $data): array {
    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('CID'),
          $this->t('Hit'),
          $this->t('Miss'),
          $this->t('Tags'),
        ],
        '#rows' => array_map(function (array $cache) {
          return [
            $cache['cache']['cid'],
            $cache['count'][CacheDataCollector::WEBPROFILER_CACHE_HIT],
            $cache['count'][CacheDataCollector::WEBPROFILER_CACHE_MISS],
            [
              'data' => [
                '#type' => 'inline_template',
                '#template' => '{{ data|raw }}',
                '#context' => [
                  'data' => count($cache['cache']['tags']) > 0 ? $this->dumpData($this->cloneVar($cache['cache']['tags'])) : '',
                ],
              ],
            ],
          ];
        }, $data),
        '#attributes' => [
          'class' => [
            'webprofiler__table',
          ],
        ],
      ],
    ];
  }

}
