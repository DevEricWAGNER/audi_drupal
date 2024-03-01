<?php

declare(strict_types = 1);

namespace Drupal\tracer\Http;

use Drupal\tracer\TracerInterface;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP client middleware to trace requests.
 */
class HttpClientMiddleware {

  /**
   * HttpClientMiddleware constructor.
   *
   * @param \Drupal\tracer\TracerInterface $tracer
   *   The tracer service.
   */
  public function __construct(
    protected readonly TracerInterface $tracer
  ) {
  }

  /**
   * Middleware callback.
   */
  public function __invoke(): callable {
    return function ($handler): callable {
      return function (RequestInterface $request, array $options) use ($handler) {
        $span = $this->tracer->start('HTTP call', (string) $request->getUri());
        $response = $handler($request, $options);
        $this->tracer->stop($span);

        return $response;
      };
    };
  }

}
