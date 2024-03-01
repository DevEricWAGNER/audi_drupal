<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Http;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;

/**
 * A stack middleware that collects data about the request.
 */
class HttpClientMiddleware {

  /**
   * List of request completed with success.
   *
   * @var array
   */
  private array $completedRequests;

  /**
   * List of failed requests.
   *
   * @var array
   */
  private array $failedRequests;

  /**
   * Store the transfer stats of the current request.
   *
   * @var \GuzzleHttp\TransferStats
   */
  private TransferStats $stats;

  /**
   * HttpClientMiddleware constructor.
   */
  public function __construct() {
    $this->completedRequests = [];
    $this->failedRequests = [];
  }

  /**
   * Invoke the middleware.
   *
   * @return \Closure
   *   The middleware closure, used to collect data about the request.
   */
  public function __invoke(): \Closure {
    return function ($handler) {
      return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
        // If on_stats callback is already set then save it
        // and call it after ours.
        $next = $options['on_stats'] ?? function (TransferStats $stats): void {
        };

        $options['on_stats'] = function (TransferStats $stats) use ($next): void {
          $this->stats = $stats;
          $next($stats);
        };

        return $handler($request, $options)->then(
          function ($response) use ($request) {
            $this->completedRequests[] = [
              'request' => $request,
              'response' => $response,
              'stats' => $this->stats,
            ];

            return $response;
          },
          function ($reason) use ($request) {
            $response = $reason instanceof RequestException
              ? $reason->getResponse()
              : NULL;

            $this->failedRequests[] = [
              'request' => $request,
              'response' => $response,
              'message' => $reason->getMessage(),
            ];

            return Create::rejectionFor($reason);
          },
        );
      };
    };
  }

  /**
   * Return the list of completed requests.
   *
   * @return array
   *   The list of completed requests.
   */
  public function getCompletedRequests(): array {
    return $this->completedRequests;
  }

  /**
   * Return the list of failed requests.
   *
   * @return array
   *   The list of failed requests.
   */
  public function getFailedRequests(): array {
    return $this->failedRequests;
  }

}
