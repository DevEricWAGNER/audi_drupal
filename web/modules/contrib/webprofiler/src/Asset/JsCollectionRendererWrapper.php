<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Asset;

use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\webprofiler\DataCollector\AssetsDataCollector;

/**
 * Collect data about rendered javascript files.
 */
class JsCollectionRendererWrapper implements AssetCollectionRendererInterface {

  /**
   * JsCollectionRendererWrapper constructor.
   *
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $assetCollectionRenderer
   *   The decorated asset collection renderer.
   * @param \Drupal\webprofiler\DataCollector\AssetsDataCollector $dataCollector
   *   The assets data collector.
   */
  public function __construct(
    private readonly AssetCollectionRendererInterface $assetCollectionRenderer,
    private readonly AssetsDataCollector $dataCollector
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function render(array $assets): array {
    $this->dataCollector->addJsAsset($assets);

    return $this->assetCollectionRenderer->render($assets);
  }

}
