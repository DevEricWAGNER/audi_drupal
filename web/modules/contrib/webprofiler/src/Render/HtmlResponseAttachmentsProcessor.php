<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Render;

use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Render\HtmlResponseAttachmentsProcessor as HtmlResponseAttachmentsProcessorBase;
use Drupal\webprofiler\DataCollector\AssetsDataCollector;

/**
 * Extends the Drupal core html_response.attachments_processor service.
 */
class HtmlResponseAttachmentsProcessor extends HtmlResponseAttachmentsProcessorBase {

  /**
   * The assets data collector.
   *
   * @var \Drupal\webprofiler\DataCollector\AssetsDataCollector
   */
  private AssetsDataCollector $dataCollector;

  /**
   * {@inheritdoc}
   */
  protected function processAssetLibraries(AttachedAssetsInterface $assets, array $placeholders): array {
    $this->dataCollector->setLibraries($assets->getLibraries());
    $this->dataCollector->setPlaceholders($placeholders);

    return parent::processAssetLibraries($assets, $placeholders);
  }

  /**
   * Set the assets data collector.
   *
   * @param \Drupal\webprofiler\DataCollector\AssetsDataCollector $data_collector
   *   The assets data collector.
   */
  public function setDataCollector(AssetsDataCollector $data_collector): void {
    $this->dataCollector = $data_collector;
  }

}
