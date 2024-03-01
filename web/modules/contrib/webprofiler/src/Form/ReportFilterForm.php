<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webprofiler\Profiler\Profiler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to filter the list of profiles.
 */
class ReportFilterForm extends FormBase {

  /**
   * The Profiler service.
   *
   * @var \Drupal\webprofiler\Profiler\Profiler
   */
  private Profiler $profiler;

  /**
   * ReportFilterForm constructor.
   *
   * @param \Drupal\webprofiler\Profiler\Profiler $profiler
   *   The Profiler service.
   */
  final public function __construct(Profiler $profiler) {
    $this->profiler = $profiler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ReportFilterForm {
    return new static(
      $container->get('webprofiler.profiler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'webprofiler_report_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['ip'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IP'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('ip'),
      '#prefix' => '<div class="form--inline clearfix">',
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('url'),
    ];

    $form['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Method'),
      '#options' => [
        '- any -' => $this->t('All'),
        'GET' => $this->t('GET'),
        'POST' => $this->t('POST'),
      ],
      '#default_value' => $this->getRequest()->query->get('method'),
    ];

    $limits = [10, 50, 100];
    $form['limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Limit'),
      '#options' => array_combine($limits, $limits),
      '#default_value' => $this->getRequest()->query->get('limit'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['filter'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#attributes' => ['class' => ['button--primary']],
    ];

    $form['actions']['clear'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clear'),
      '#attributes' => ['class' => ['button--secondary']],
      '#suffix' => '</div>',
      '#submit' => ['::clear'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $ip = $form_state->getValue('ip');
    $url = $form_state->getValue('url');
    $method = $form_state->getValue('method');
    $limit = $form_state->getValue('limit');

    $url = new Url('webprofiler.admin_list', [], [
      'query' => [
        'ip' => $ip,
        'url' => $url,
        'method' => $method,
        'limit' => $limit,
      ],
    ]);

    $form_state->setRedirectUrl($url);
  }

  /**
   * Clear the filters.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function clear(array &$form, FormStateInterface $form_state): void {
    $this->profiler->purge();

    $url = new Url('webprofiler.admin_list');
    $form_state->setRedirectUrl($url);
  }

}
