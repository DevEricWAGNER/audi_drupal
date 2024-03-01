<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\Entity\EntityDecorator;
use Drupal\webprofiler\Entity\EntityTypeManagerWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects blocks data.
 */
class BlocksDataCollector extends DataCollector implements HasPanelInterface {

  use StringTranslationTrait;

  /**
   * BlocksDataCollector constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   The Entity type manager service.
   */
  public function __construct(private readonly EntityTypeManagerInterface $entityManager) {
    $this->data['blocks']['loaded'] = [];
    $this->data['blocks']['rendered'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'blocks';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $storage = $this->entityManager->getStorage('block');

    assert($this->entityManager instanceof EntityTypeManagerWrapper);
    $loaded = $this->entityManager->getLoaded('config', 'block');
    $rendered = $this->entityManager->getRendered('block');

    if ($loaded != NULL) {
      $this->data['blocks']['loaded'] = $this->getBlocksData($loaded, $storage);
    }

    if ($rendered != NULL) {
      $this->data['blocks']['rendered'] = $this->getBlocksData($rendered, $storage);
    }
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Return a list of rendered blocks.
   *
   * @return array
   *   A list of rendered blocks.
   */
  public function getRenderedBlocks(): array {
    return $this->data['blocks']['rendered'];
  }

  /**
   * Return the number of rendered blocks.
   *
   * @return int
   *   The number of rendered blocks.
   */
  public function getRenderedBlocksCount(): int {
    return count($this->getRenderedBlocks());
  }

  /**
   * Return a list of loaded blocks.
   *
   * @return array
   *   A list of loaded blocks.
   */
  public function getLoadedBlocks(): array {
    return $this->data['blocks']['loaded'];
  }

  /**
   * Return the number of loaded blocks.
   *
   * @return int
   *   The number of rendered blocks.
   */
  public function getLoadedBlocksCount(): int {
    return count($this->getLoadedBlocks());
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $tabs = [
      [
        'label' => 'Loaded',
        'content' => $this->renderBlocks($this->getLoadedBlocks(), 'Loaded'),
      ],
      [
        'label' => 'Rendered',
        'content' => $this->renderBlocks($this->getRenderedBlocks(), 'Rendered'),
      ],
    ];

    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => $tabs,
    ];
  }

  /**
   * Return the data to store about blocks.
   *
   * @param \Drupal\webprofiler\Entity\EntityDecorator $decorator
   *   An entity decorator.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The block storage service.
   *
   * @return array
   *   The data to store about blocks.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function getBlocksData(EntityDecorator $decorator, EntityStorageInterface $storage): array {
    $blocks = [];

    /** @var \Drupal\block\BlockInterface $block */
    foreach ($decorator->getEntities() as $block) {
      if ($block != NULL) {
        /** @var \Drupal\block\Entity\Block|null $entity */
        $entity = $storage->load($block->get('id'));

        if ($entity != NULL) {
          $route = '';
          if ($entity->hasLinkTemplate('edit-form')) {
            $route = $entity->toUrl('edit-form')->toString();
          }

          $id = $block->get('id');
          $blocks[$id] = [
            'id' => $id,
            'region' => $block->getRegion(),
            'status' => $block->get('status'),
            'theme' => $block->getTheme(),
            'plugin' => $block->get('plugin'),
            'settings' => $block->get('settings'),
            'route' => $route,
          ];
        }
      }
    }

    return $blocks;
  }

  /**
   * Render a list of blocks.
   *
   * @param array $blocks
   *   The list of blocks to render.
   * @param string $label
   *   The list's label.
   *
   * @return array
   *   The render array of the list of blocks.
   */
  private function renderBlocks(array $blocks, string $label): array {
    if (count($blocks) == 0) {
      return [
        $label => [
          '#markup' => '<p>' . $this->t('No @label blocks collected',
              ['@label' => $label]) . '</p>',
        ],
      ];
    }

    $rows = [];
    foreach ($blocks as $block) {
      $rows[] = [
        $block['id'],
        $block['settings']['label'],
        $block['region'] ?? 'No region',
        $block['settings']['provider'],
        $block['theme'],
        $block['status'] ? $this->t('Enabled') : $this->t('Disabled'),
        $block['plugin'],
      ];
    }

    return [
      $label => [
        '#theme' => 'webprofiler_dashboard_section',
        '#data' => [
          '#type' => 'table',
          '#header' => [
            $this->t('ID'),
            $this->t('Label'),
            $this->t('Region'),
            $this->t('Source'),
            $this->t('Theme'),
            $this->t('Status'),
            $this->t('Plugin'),
          ],
          '#rows' => $rows,
          '#attributes' => [
            'class' => [
              'webprofiler__table',
            ],
          ],
          '#sticky' => TRUE,
        ],
      ],
    ];
  }

}
