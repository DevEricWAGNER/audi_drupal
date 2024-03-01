<?php

namespace Drupal\info_connection\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for entering connection information.
 */
class InfoConnectionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'info_connection_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['connection_info'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Connection Information'),
      '#description' => $this->t('Enter the connection information here.'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

    /**
   * Implements hook_menu().
   */
  function info_connection_menu() {
    $items['admin/config/system/info_connection'] = [
      'title' => 'Connection Information',
      'description' => 'Manage connection information.',
      'route_name' => 'info_connection.form',
      'weight' => 10,
      'context' => 'menu.local_actions',
    ];

    return $items;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Récupérer les données soumises.
    $connection_info = $form_state->getValue('connection_info');

    // Enregistrer les données dans l'état de Drupal.
    \Drupal::state()->set('info_connection', $connection_info);

    // Message de confirmation.
    $this->messenger()->addMessage($this->t('Connection information saved successfully.'));

    // Invalider le cache pour le bloc.
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['info_connection_block']);
  }


}
