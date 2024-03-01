<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects assets data.
 */
class AssetsDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  /**
   * AssetDataCollector constructor.
   *
   * @param string $root
   *   The app root.
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $libraryDiscovery
   *   The library discovery service.
   */
  public function __construct(
    private readonly string $root,
    private readonly LibraryDiscoveryInterface $libraryDiscovery
  ) {
    $this->data['js'] = [];
    $this->data['css'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'assets';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $this->data['assets']['installation_path'] = $this->root . '/';
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Add a javascript asset to collected data.
   *
   * @param array $jsAsset
   *   A javascript asset.
   */
  public function addJsAsset(array $jsAsset): void {
    $this->data['js'] = NestedArray::mergeDeepArray([
      $jsAsset,
      $this->data['js'],
    ]);
  }

  /**
   * Add a css asset to collected data.
   *
   * @param array $cssAsset
   *   A css asset.
   */
  public function addCssAsset(array $cssAsset): void {
    $this->data['css'] = NestedArray::mergeDeepArray([
      $cssAsset,
      $this->data['css'],
    ]);
  }

  /**
   * Set the libraries to collected data.
   *
   * @param array $libraries
   *   A list of libraries.
   */
  public function setLibraries(array $libraries): void {
    sort($libraries);

    $data = [];
    foreach ($libraries as $library) {
      [$extension, $name] = explode('/', $library);
      $definition = $this->libraryDiscovery->getLibraryByName($extension, $name);
      $data[$library] = $definition;
    }

    $this->data['libraries'] = $data;
  }

  /**
   * Set the placeholders to collected data.
   *
   * @param array $placeholders
   *   A list of placeholders.
   */
  public function setPlaceholders(array $placeholders): void {
    $this->data['placeholders'] = $placeholders;
  }

  /**
   * Return the number of css files used in page.
   *
   * @return int
   *   The number of css files used in page.
   */
  public function getCssCount(): int {
    return count($this->data['css']);
  }

  /**
   * Return the number of javascript files used in page.
   *
   * @return int
   *   The number of javascript files used in page.
   */
  public function getJsCount(): int {
    return count($this->data['js']) - 1;
  }

  /**
   * Return the number of libraries used in page.
   *
   * @return int
   *   The number of libraries used in page.
   */
  public function getLibrariesCount(): int {
    return count($this->data['libraries']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => [
        [
          'label' => $this->t('CSS'),
          'content' => $this->renderCss($this->data['css']),
        ],
        [
          'label' => $this->t('JS'),
          'content' => $this->renderJs($this->data['js']),
        ],
        [
          'label' => $this->t('Settings'),
          'content' => $this->renderSettings($this->data['js'] ?? ['DrupalSettings']),
        ],
        [
          'label' => $this->t('Libraries'),
          'content' => $this->renderLibraries($this->data['libraries']),
        ],
      ],
    ];
  }

  /**
   * Render a list of CSS files.
   *
   * @param array $data
   *   A list of CSS files.
   *
   * @return array
   *   The render array of the list of CSS files.
   */
  private function renderCss(array $data): array {
    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Asset'),
          $this->t('Version'),
          $this->t('Type'),
          $this->t('Media'),
        ],
        '#rows' => array_map(function ($asset) {
          return [
            $asset['data'],
            $asset['version'],
            $asset['type'],
            $asset['media'],
          ];
        }, $data),
        '#attributes' => [
          'class' => [
            'webprofiler__table',
          ],
        ],
        '#sticky' => TRUE,
      ],
    ];
  }

  /**
   * Render a list of javascript files.
   *
   * @param array $data
   *   A list of javascript files.
   *
   * @return array
   *   The render array of the list of javascript files.
   */
  private function renderJs(array $data): array {
    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Asset'),
          $this->t('Version'),
          $this->t('Type'),
          $this->t('Scope'),
        ],
        '#rows' => array_map(function ($asset) {
          return [
            $asset['data'],
            $asset['version'],
            $asset['type'],
            $asset['scope'],
          ];
        }, array_filter($data, function ($asset) {
          return $asset['type'] !== 'setting';
        })),
        '#attributes' => [
          'class' => [
            'webprofiler__table',
          ],
        ],
        '#sticky' => TRUE,
      ],
    ];
  }

  /**
   * Render the DrupalSettings array.
   *
   * @param array $settings
   *   The DrupalSettings array.
   *
   * @return array
   *   The render array of the DrupalSettings array.
   */
  private function renderSettings(array $settings): array {
    return [
      '#type' => 'inline_template',
      '#template' => '{{ data|raw }}',
      '#context' => [
        'data' => array_key_exists('drupalSettings', $settings) ? $this->dumpData($this->cloneVar($settings['drupalSettings'])) : 'n/a',
      ],
    ];
  }

  /**
   * Render the Libraries array.
   *
   * @param array $libraries
   *   The Libraries array.
   *
   * @return array
   *   The render array of the Libraries array.
   */
  private function renderLibraries(array $libraries): array {
    return [
      '#theme' => 'webprofiler_dashboard_libraries',
      '#libraries' => $libraries,
    ];
  }

}
