<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Csp;

/**
 * Generates Content-Security-Policy nonce.
 *
 * @internal
 */
class NonceGenerator {

  /**
   * Generates Content-Security-Policy nonce.
   *
   * @return string
   *   A nonce.
   *
   * @throws \Exception
   */
  public function generate(): string {
    return bin2hex(random_bytes(16));
  }

}
