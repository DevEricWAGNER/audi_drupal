<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Wrap the form builder to collect form data.
 */
class FormBuilderWrapper extends FormBuilder {

  /**
   * List of build forms.
   *
   * @var array
   */
  private array $buildForms = [];

  /**
   * Return the list of build forms.
   *
   * @return array
   *   The list of build forms.
   */
  public function getBuildForm(): array {
    return $this->buildForms;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareForm($form_id, &$form, FormStateInterface &$form_state) {
    parent::prepareForm($form_id, $form, $form_state);

    $elements = $this->extractElement($form);
    $buildInfo = $form_state->getBuildInfo();

    $class = get_class($buildInfo['callback_object']);
    try {
      $method = new \ReflectionMethod($class, 'buildForm');

      $this->buildForms[$buildInfo['form_id']] = [
        'class' => [
          'class' => $class,
          'method' => 'buildForm',
          'file' => $method->getFileName(),
          'line' => $method->getStartLine(),
        ],
        'elements' => $elements,
        'action' => $form['#action'],
        'method' => $form['#method'],
      ];
    }
    catch (\ReflectionException $e) {
    }

    return $form;
  }

  /**
   * Extract element information from the form.
   *
   * @param array $form
   *   The form.
   *
   * @return array
   *   Element information from the form.
   */
  private function extractElement(array $form): array {
    $elements = [];

    $children = Element::children($form);

    foreach ($children as $child) {
      $elements[$child]['#title'] = $form[$child]['#title'] ?? NULL;
      $elements[$child]['#access'] = $form[$child]['#access'] ?? NULL;
      $elements[$child]['#type'] = $form[$child]['#type'] ?? NULL;

      $elements[$child]['#children'] = $this->extractElement($form[$child]);
    }

    return $elements;
  }

}
