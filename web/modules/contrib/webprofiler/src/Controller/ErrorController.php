<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\webprofiler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders error or exception pages from a given FlattenException.
 */
class ErrorController implements ContainerInjectionInterface {

  /**
   * ErrorController constructor.
   *
   * @param \Drupal\webprofiler\ErrorRenderer\HtmlErrorRenderer $htmlErrorRenderer
   *   The error renderer.
   */
  final public function __construct(
    private readonly HtmlErrorRenderer $htmlErrorRenderer,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ErrorController {
    return new static(
      $container->get('webprofiler.error_renderer'),
    );
  }

  /**
   * Invokes the controller.
   *
   * @param \Throwable $exception
   *   The exception to render.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function __invoke(\Throwable $exception): Response {
    $exception = $this->htmlErrorRenderer->render($exception);

    return new Response($exception->getAsString(), $exception->getStatusCode(), $exception->getHeaders());
  }

}
