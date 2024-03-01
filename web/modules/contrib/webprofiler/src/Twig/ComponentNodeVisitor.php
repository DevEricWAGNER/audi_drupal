<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Twig;

use Drupal\sdc\ComponentPluginManager;
use Drupal\sdc\Exception\ComponentNotFoundException;
use Drupal\sdc\Plugin\Component;
use Drupal\webprofiler\DataCollector\ThemeDataCollector;
use Twig\Environment;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Provides a ComponentNodeVisitor to collects data about components.
 */
final class ComponentNodeVisitor implements NodeVisitorInterface {

  /**
   * Creates a new ComponentNodeVisitor object.
   *
   * @param \Drupal\sdc\ComponentPluginManager $pluginManager
   *   The plugin manager for components.
   * @param \Drupal\webprofiler\DataCollector\ThemeDataCollector $dataCollector
   *   The data collector for theme data.
   */
  public function __construct(
    private readonly ComponentPluginManager $pluginManager,
    private readonly ThemeDataCollector $dataCollector,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function enterNode(Node $node, Environment $env): Node {
    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function leaveNode(Node $node, Environment $env): ?Node {
    if (!$node instanceof ModuleNode) {
      return $node;
    }
    $component = $this->getComponent($node);

    if ($component == NULL) {
      return $node;
    }

    $this->dataCollector->addComponent($component);

    return $node;
  }

  /**
   * Finds the SDC for the current module node.
   *
   * @param \Twig\Node\Node $node
   *   The node.
   *
   * @return \Drupal\sdc\Plugin\Component|null
   *   The component, if any.
   */
  protected function getComponent(Node $node): ?Component {
    $component_id = $node->getTemplateName();
    if (!preg_match('/^[a-z]([a-zA-Z0-9_-]*[a-zA-Z0-9])*:[a-z]([a-zA-Z0-9_-]*[a-zA-Z0-9])*$/', $component_id)) {
      return NULL;
    }
    try {
      return $this->pluginManager->find($component_id);
    }
    catch (ComponentNotFoundException $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority(): int {
    return 250;
  }

}
