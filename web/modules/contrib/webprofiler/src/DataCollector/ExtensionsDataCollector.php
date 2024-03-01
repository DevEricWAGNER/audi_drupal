<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects extensions data.
 */
class ExtensionsDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  /**
   * ExtensionDataCollector constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   The theme handler.
   * @param string $root
   *   The app root.
   */
  public function __construct(
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly ThemeHandlerInterface $themeHandler,
    private readonly string $root
  ) {
    $this->data['modules'] = [];
    $this->data['themes'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'extensions';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $modules = $this->moduleHandler->getModuleList();
    $themes = $this->themeHandler->listInfo();

    $this->data['count'] = count($modules) + count($themes);
    $this->data['modules'] = $this->extractData($modules);
    $this->data['themes'] = $this->extractData($themes);
  }

  /**
   * Extracts data from extensions.
   *
   * @param array $extensions
   *   The extensions.
   *
   * @return array
   *   The extracted data.
   */
  private function extractData(array $extensions): array {
    return array_map(function (Extension $extension) {
      return [
        'name' => $extension->getName(),
        'path' => $extension->getPath(),
        'info' => $this->root . '/' . $extension->getPathname(),
        'experimental' => $extension->isExperimental(),
        'obsolete' => $extension->isObsolete(),
      ];
    }, $extensions);
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Returns the total number of active extensions.
   *
   * @return int
   *   The total number of active extensions.
   */
  public function getExtensionsCount(): int {
    return $this->data['count'] ?? 0;
  }

  /**
   * Returns the total number of active modules.
   *
   * @return int
   *   The total number of active modules.
   */
  public function getModulesCount(): int {
    return count($this->data['modules']);
  }

  /**
   * Returns the total number of active themes.
   *
   * @return int
   *   The total number of active themes.
   */
  public function getThemesCount(): int {
    return count($this->data['themes']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => [
        [
          'label' => $this->t('Modules'),
          'content' => $this->renderExtensions($this->data['modules']),
        ],
        [
          'label' => $this->t('Themes'),
          'content' => $this->renderExtensions($this->data['themes']),
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
  private function renderExtensions(array $data): array {
    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Path'),
          $this->t('Info file'),
          $this->t('Experimental'),
          $this->t('Obsolete'),
        ],
        '#rows' => array_map(function (array $extension) {
          return [
            $extension['name'],
            $extension['path'],
            $extension['info'],
            $extension['experimental'] ? $this->t('Yes') : $this->t('No'),
            $extension['obsolete'] ? $this->t('Yes') : $this->t('No'),
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

}
