<?php

declare(strict_types = 1);

namespace Drupal\tracer\Controller;

use Drupal\tracer\TracerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * Controller resolver that traces time spent.
 */
class TraceableControllerResolver implements ControllerResolverInterface {

  /**
   * TraceableControllerResolver constructor.
   *
   * @param \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $resolver
   *   The resolver to wrap.
   * @param \Drupal\tracer\TracerInterface $tracer
   *   The tracer service.
   */
  public function __construct(
    protected readonly ControllerResolverInterface $resolver,
    protected readonly TracerInterface $tracer
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getController(Request $request): callable|FALSE {
    $span = $this->tracer->start('get_controller', $request->getUri());
    $ret = $this->resolver->getController($request);
    $this->tracer->stop($span);

    return $ret;
  }

}
