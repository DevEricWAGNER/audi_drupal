<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register data about existing services.
 */
class ServicePass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    if (FALSE === $container->hasDefinition('webprofiler.services')) {
      return;
    }

    $definition = $container->getDefinition('webprofiler.services');
    $graph = $container->getCompiler()->getServiceReferenceGraph();

    $definition->addMethodCall('setServices', [$this->extractData($container, $graph)]);
  }

  /**
   * Extract service data from the service container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   The service container.
   * @param \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph $graph
   *   The service reference graph.
   *
   * @return array
   *   Service data.
   */
  private function extractData(ContainerBuilder $container, ServiceReferenceGraph $graph): array {
    $data = [];

    foreach ($container->getDefinitions() as $id => $definition) {
      $inEdges = [];
      $outEdges = [];

      if ($graph->hasNode($id)) {
        $node = $graph->getNode($id);

        foreach ($node->getInEdges() as $edge) {
          /** @var \Symfony\Component\DependencyInjection\Reference|null $edgeValue */
          $edgeValue = $edge->getValue();

          $inEdges[] = [
            'id' => $edge->getSourceNode()->getId(),
            'invalidBehavior' => $edgeValue?->getInvalidBehavior(),
          ];
        }

        foreach ($node->getOutEdges() as $edge) {
          /** @var \Symfony\Component\DependencyInjection\Reference|null $edgeValue */
          $edgeValue = $edge->getValue();

          $outEdges[] = [
            'id' => $edge->getDestNode()->getId(),
            'invalidBehavior' => $edgeValue?->getInvalidBehavior(),
          ];
        }
      }

      $file = NULL;
      $class = $definition->getClass();
      if ($class !== NULL) {
        try {
          $reflectedClass = new \ReflectionClass($class);
          $file = $reflectedClass->getFileName();
        }
        catch (\ReflectionException $e) {
          $file = NULL;
        }
      }

      $tags = $definition->getTags();
      $public = $definition->isPublic();
      $synthetic = $definition->isSynthetic();

      $data[$id] = [
        'inEdges' => $inEdges,
        'outEdges' => $outEdges,
        'value' => [
          'class' => $class,
          'file' => $file,
          'id' => $id,
          'tags' => $tags,
          'public' => $public,
          'synthetic' => $synthetic,
        ],
      ];
    }

    return $data;
  }

}
