<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DumpTrait;
use Drupal\webprofiler\MethodData;

/**
 * Base class for dashboard panels.
 */
trait PanelTrait {

  use StringTranslationTrait, DumpTrait;

  /**
   * Render data in an array as HTML table.
   *
   * @param array $data
   *   The data to render.
   * @param string|null $label
   *   The table label.
   * @param callable|null $element_converter
   *   An optional function to convert all elements of data before rendering.
   *   If NULL fallback to PanelBase::dumpData.
   *
   * @return array
   *   A render array.
   */
  protected function renderTable(
    array $data,
    ?string $label = NULL,
    callable $element_converter = NULL
  ): array {
    if (count($data) == 0) {
      return [];
    }

    if ($element_converter == NULL) {
      $element_converter = [$this, 'dumpData'];
    }

    $rows = [];
    foreach ($data as $key => $el) {
      $rows[] = [
        [
          'data' => $key,
          'class' => 'webprofiler__key',
        ],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ data|raw }}',
            '#context' => [
              'data' => $element_converter($el),
            ],
          ],
          'class' => 'webprofiler__value',
        ],
      ];
    }

    $section = [
      '#theme' => 'webprofiler_dashboard_section',
      '#title' => $label,
      '#data' => [
        '#type' => 'table',
        '#header' => [$this->t('Name'), $this->t('Value')],
        '#rows' => $rows,
        '#attributes' => [
          'class' => [
            'webprofiler__table',
          ],
        ],
      ],
    ];

    if ($label != NULL) {
      $section['#title'] = $label;
    }

    return $section;
  }

  /**
   * Render a link to a file.
   *
   * @param string|false $file
   *   The file path.
   * @param int $line
   *   The file line.
   * @param string $label
   *   A label to display.
   *
   * @return array
   *   A render array for the link.
   */
  protected function renderClassLink(string|false $file, int $line, string $label): array {
    $flf = \Drupal::service('webprofiler.file_link_formatter');

    return [
      '#type' => 'inline_template',
      '#template' => '<a href="{{ href }}">{{ label }}</a>',
      '#context' => [
        'href' => $flf->format($file != NULL ? $file : '', $line),
        'label' => $label,
      ],
    ];
  }

  /**
   * Render a link to a file from a MethodData object.
   *
   * @param \Drupal\webprofiler\MethodData $method
   *   MethodData object.
   *
   * @return array
   *   A render array for the link.
   */
  protected function renderClassLinkFromMethodData(MethodData $method): array {
    return $this->renderClassLink($method->getFile(), $method->getLine(), $method->getClass() . '::' . $method->getMethod());
  }

  /**
   * Render a time value.
   *
   * @param float $time
   *   The time value.
   * @param string $unit
   *   The time unit.
   *
   * @return string
   *   The rendered time value.
   */
  protected function renderTime(float $time, string $unit = 'ms'): string {
    $time = round($time * 100, 2) / 100;

    return $time . ' ' . $unit;
  }

}
