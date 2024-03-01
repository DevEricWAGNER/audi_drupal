<?php

declare(strict_types = 1);

namespace Drupal\tracer\StackMiddleware;

use Drupal\tracer\TracerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Stack middleware to trace the request.
 */
class TracesMiddleware implements HttpKernelInterface {

  /**
   * Constructs a WebprofilerMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The decorated kernel.
   * @param \Drupal\tracer\TracerInterface $tracer
   *   The tracer service.
   */
  public function __construct(
    protected readonly HttpKernelInterface $httpKernel,
    protected readonly TracerInterface $tracer
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = TRUE): Response {
    $rootSpan = $this->tracer->start('root', 'root');
    $this->tracer->openSection($rootSpan);

    $response = $this->httpKernel->handle($request, $type, $catch);

    $this->tracer->stop($rootSpan);
    $this->tracer->closeSection($rootSpan);

    return $response;
  }

}
