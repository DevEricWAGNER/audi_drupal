<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Entity;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Wrap the entity type manager service to collect loaded and rendered entities.
 */
class EntityTypeManagerWrapper extends EntityTypeManager implements EntityTypeManagerInterface, ContainerAwareInterface {

  /**
   * Loaded entities.
   *
   * @var array
   */
  private array $loaded;

  /**
   * Rendered entities.
   *
   * @var array
   */
  private array $rendered;

  /**
   * The original entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *
   * @phpstan-ignore-next-line
   */
  private EntityTypeManagerInterface $entityManager;

  /**
   * EntityTypeManagerWrapper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The original entity manager service.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to use.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   * @param \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface $entity_last_installed_schema_repository
   *   The entity last installed schema repository.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, \Traversable $namespaces, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache, TranslationInterface $string_translation, ClassResolverInterface $class_resolver, EntityLastInstalledSchemaRepositoryInterface $entity_last_installed_schema_repository) {
    $this->entityManager = $entity_manager;

    $this->setCacheBackend($cache, 'entity_type', ['entity_types']);
    $this->alterInfo('entity_type');

    parent::__construct($namespaces, $module_handler, $cache, $string_translation, $class_resolver, $entity_last_installed_schema_repository);
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage($entity_type_id) {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $handler */
    $handler = $this->getHandler($entity_type_id, 'storage');
    $entity_kind = ($handler instanceof ConfigEntityStorageInterface) ? 'config' : 'content';

    if (!isset($this->loaded[$entity_kind][$entity_type_id])) {
      $handler = $this->getStorageDecorator($entity_type_id, $handler);
      $this->loaded[$entity_kind][$entity_type_id] = $handler;
    }
    else {
      $handler = $this->loaded[$entity_kind][$entity_type_id];
    }

    return $handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewBuilder($entity_type_id) {
    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $handler */
    $handler = $this->getHandler($entity_type_id, 'view_builder');

    if ($handler instanceof EntityViewBuilderInterface) {
      if (!isset($this->rendered[$entity_type_id])) {
        $handler = new EntityViewBuilderDecorator($handler);
        $this->rendered[$entity_type_id] = $handler;
      }
      else {
        $handler = $this->rendered[$entity_type_id];
      }
    }

    return $handler;
  }

  /**
   * Return loaded entities.
   *
   * @param string $entity_kind
   *   The kind of the entity: config or content.
   * @param string $entity_type
   *   The entity's type.
   *
   * @return ConfigEntityStorageDecorator|null
   *   Loaded entities.
   */
  public function getLoaded(string $entity_kind, string $entity_type): ConfigEntityStorageDecorator|NULL {
    return $this->loaded[$entity_kind][$entity_type] ?? NULL;
  }

  /**
   * Return rendered entities.
   *
   * @param string $entity_type
   *   The entity's type.
   *
   * @return EntityViewBuilderDecorator|null
   *   Rendered entities.
   */
  public function getRendered(string $entity_type): EntityViewBuilderDecorator|NULL {
    return $this->rendered[$entity_type] ?? NULL;
  }

  /**
   * Prevents the service container from being serialized.
   *
   * @return string[]
   *   The properties to serialize.
   */
  public function __sleep(): array {
    return ['loaded', 'rendered'];
  }

  /**
   * Return a decorator for the storage handler.
   *
   * @param string $entity_type
   *   The entity's type.
   * @param object $handler
   *   The original storage handler.
   *
   * @return object
   *   A decorator for the storage handler.
   */
  private function getStorageDecorator(string $entity_type, object $handler): object {
    // Loaded this way to avoid circular references.
    /** @var \Drupal\webprofiler\DecoratorGeneratorInterface $decoratorGenerator */
    // @phpstan-ignore-next-line
    $decoratorGenerator = \Drupal::service('webprofiler.config_entity_storage_decorator_generator');

    $decorators = $decoratorGenerator->getDecorators();

    $storage = PhpStorageFactory::get('webprofiler');
    if ($handler instanceof ConfigEntityStorageInterface) {
      if (array_key_exists($entity_type, $decorators)) {
        $storage->load($entity_type);
        if (!class_exists($decorators[$entity_type])) {
          try {
            $decoratorGenerator->generate();
            $storage->load($entity_type);
          }
          catch (\Exception $e) {
            return $handler;
          }
        }

        return new $decorators[$entity_type]($handler);
      }

      return new ConfigEntityStorageDecorator($handler);
    }

    return $handler;
  }

}
