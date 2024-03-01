<?php

namespace Drupal\info_connection\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Connection Info Block' block.
 *
 * @Block(
 *   id = "custom_connection_info_block",
 *   admin_label = @Translation("Connection Info Block"),
 *   category = @Translation("Custom")
 * )
 */
class InfoConnectionBlock  extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new ConnectionInfoBlock instance.
   *
   * @param array $configuration
   *   The block configuration.
   * @param string $plugin_id
   *   The plugin_id for the block.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the connection info from the state.
    $connection_info = \Drupal::state()->get('info_connection', '');

    // Build the block content.
    $build = [
      '#markup' => $this->t('Connection information: @info', ['@info' => $connection_info]),
      '#cache' => [
        'max-age' => 0, // Cache par utilisateur authentifié.
        // cache par utilisateur non authentifié.
        'contexts' => ['user'],
      ],
    ];

    return $build;
  }

}
