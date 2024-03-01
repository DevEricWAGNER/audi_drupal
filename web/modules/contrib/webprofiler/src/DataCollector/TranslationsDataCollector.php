<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\webprofiler\StringTranslation\TranslationManagerWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects translations data.
 */
class TranslationsDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait;

  /**
   * TranslationsDataCollector constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The translation service.
   */
  public function __construct(
    private readonly TranslationInterface $translation,
  ) {
    $this->data['translations']['translated'] = [];
    $this->data['translations']['untranslated'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    if ($this->translation instanceof TranslationManagerWrapper) {
      $this->data['translations']['translated'] = $this->translation->getTranslated();
      $this->data['translations']['untranslated'] = $this->translation->getUntranslated();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'translations';
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Return the number of translated strings.
   *
   * @return int
   *   The number of translated strings.
   */
  public function getTranslatedCount(): int {
    return count($this->data['translations']['translated']);
  }

  /**
   * Return the number of untranslated strings.
   *
   * @return int
   *   The number of untranslated strings.
   */
  public function getUntranslatedCount(): int {
    return count($this->data['translations']['untranslated']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => [
        [
          'label' => $this->t('Translated'),
          'content' => $this->renderTranslated($this->data['translations']['translated']),
        ],
        [
          'label' => $this->t('Untranslated'),
          'content' => $this->renderUntranslated($this->data['translations']['untranslated']),
        ],
      ],
    ];
  }

  /**
   * Render a list of translated strings.
   *
   * @param array $translated
   *   A list of translated strings.
   *
   * @return array
   *   The render array of the list of translated strings.
   */
  private function renderTranslated(array $translated): array {
    array_walk(
      $translated,
      function (&$key, $data): void {
        $key = [
          $data,
          $key,
          [
            'data' => [
              '#type' => 'inline_template',
              '#template' => '<a href="{{ link }}" target="_blank">{{ "Edit"|t }}</a>',
              '#context' => [
                'link' => Url::fromRoute('locale.translate_page', ['string' => $data])
                  ->toString(),
              ],
            ],
          ],
        ];
      },
    );

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Original'),
          $this->t('Translation'),
          $this->t('Action'),
        ],
        '#rows' => $translated,
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
   * Render a list of untranslated strings.
   *
   * @param array $untranslated
   *   A list of untranslated strings.
   *
   * @return array
   *   The render array of the list of untranslated strings.
   */
  private function renderUntranslated(array $untranslated): array {
    array_walk(
      $untranslated,
      function (&$key, $data): void {
        $key = [
          $data,
          [
            'data' => [
              '#type' => 'inline_template',
              '#template' => '<a href="{{ link }}" target="_blank">{{ "Translate"|t }}</a>',
              '#context' => [
                'link' => Url::fromRoute('locale.translate_page', ['string' => $data])
                  ->toString(),
              ],
            ],
          ],
        ];
      },
    );

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Original'),
          $this->t('Action'),
        ],
        '#rows' => $untranslated,
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
