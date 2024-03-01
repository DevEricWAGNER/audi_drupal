<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\Form\FormBuilderWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects forms data.
 */
class FormsDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait, PanelTrait;

  /**
   * FormsDataCollector constructor.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   */
  public function __construct(private readonly FormBuilderInterface $formBuilder) {
    $this->data['forms'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'forms';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $this->data['forms'] = [];

    if ($this->formBuilder instanceof FormBuilderWrapper) {
      $this->data['forms'] = $this->formBuilder->getBuildForm();
    }
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Return the list of collected forms.
   *
   * @return array
   *   The list of collected forms.
   */
  public function getForms(): array {
    return $this->data['forms'];
  }

  /**
   * Return the number of forms in the page.
   *
   * @return int
   *   The number of forms in the page.
   */
  public function getFormsCount(): int {
    return count($this->getForms());
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $build = [];
    $forms = $this->data['forms'];

    if (count($forms) == 0) {
      return [
        '#markup' => '<p>' . $this->t('No forms collected') . '</p>',
      ];
    }

    foreach ($forms as $id => $form) {
      $build[] = $this->renderForm($form, $id);
    }

    return $build;
  }

  /**
   * Render all elements of a form.
   *
   * @param array $form
   *   The form.
   * @param string $form_id
   *   The form id.
   *
   * @return array
   *   The render array for the form.
   */
  public function renderForm(array $form, string $form_id): array {
    $rows = $this->renderElement($form['elements']);

    return [
      '#theme' => 'webprofiler_dashboard_section',
      '#title' => sprintf('%s (%s:%s)', $form_id, $form['class']['class'], $form['class']['method']),
      '#data' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Title'),
          $this->t('Type'),
          $this->t('Access'),
        ],
        '#rows' => $rows,
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
   * Render a single form element.
   *
   * @param array $elements
   *   Form elements.
   * @param string $parent
   *   Internal use. The parent element name.
   *
   * @return array
   *   A row for the table.
   */
  public function renderElement(array $elements, string $parent = ''): array {
    $rows = [];

    foreach ($elements as $name => $element) {
      $label = $parent == '' ? $name : implode(' > ', [$parent, $name]);

      $rows[] = [
        $label,
        $element['#title'],
        $element['#type'],
        $element['#access'] ? 'Yes' : 'No',
      ];

      if (isset($element['#children'])) {
        $rows = array_merge($rows, $this->renderElement($element['#children'], $label));
      }
    }

    return $rows;
  }

}
