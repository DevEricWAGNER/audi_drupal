<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Collects Drupal data.
 */
class DrupalDataCollector extends DataCollector implements LateDataCollectorInterface {

  /**
   * DrupalDataCollector constructor.
   *
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirectDestination
   *   The Redirect destination service.
   * @param string $drupalProfile
   *   The installed profile.
   */
  public function __construct(
    private readonly RedirectDestinationInterface $redirectDestination,
    private readonly string $drupalProfile
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $this->data = [
      'token' => $response->headers->get('X-Debug-Token'),
      'drupal_version' => \Drupal::VERSION,
      'drupal_profile' => $this->drupalProfile,
      'webprofiler_config_url' => (new Url('webprofiler.settings', [], ['query' => $this->redirectDestination->getAsArray()]))->toString(),
      'php_version' => \PHP_VERSION,
      'php_architecture' => \PHP_INT_SIZE * 8,
      'php_timezone' => date_default_timezone_get(),
      'xdebug_enabled' => \extension_loaded('xdebug'),
      'apcu_enabled' => \extension_loaded('apcu') && filter_var(ini_get('apc.enabled'), \FILTER_VALIDATE_BOOLEAN),
      'zend_opcache_enabled' => \extension_loaded('Zend OPcache') && filter_var(ini_get('opcache.enable'), \FILTER_VALIDATE_BOOLEAN),
      'sapi_name' => \PHP_SAPI,
    ];

    $this->addGitInfo($this->data);

    if (preg_match('~^(\d+(?:\.\d+)*)(.+)?$~', $this->data['php_version'], $matches)) {
      $this->data['php_version'] = $matches[1];

      if (isset($matches[2])) {
        $this->data['php_version_extra'] = $matches[2];
      }
    }

    // If OpenTelemetry is present, add the TraceId to collected data.
    $abstract_span_class = '\OpenTelemetry\API\Trace\AbstractSpan';
    if (class_exists($abstract_span_class)) {
      $this->data['trace_id'] = $abstract_span_class::getCurrent()->getContext()->getTraceId();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'drupal';
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect() {
    $this->data = $this->cloneVar($this->data);
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * Gets the token.
   */
  public function getToken(): ?string {
    return $this->data['token'];
  }

  /**
   * Gets the Symfony version.
   */
  public function getDrupalVersion(): string {
    return $this->data['drupal_version'];
  }

  /**
   * Gets the Symfony version.
   */
  public function getDrupalProfile(): string {
    return $this->data['drupal_profile'];
  }

  /**
   * Gets the Webprofiler config url.
   */
  public function getWebprofilerConfigUrl(): string {
    return $this->data['webprofiler_config_url'];
  }

  /**
   * Gets the git commit info, if any.
   */
  public function getGitCommit(): ?string {
    return $this->data['git_commit'] ?? 'n/a';
  }

  /**
   * Gets the git commit SHA, if any.
   */
  public function getAbbrGitCommit(): ?string {
    return $this->data['abbr_git_commit'] ?? 'n/a';
  }

  /**
   * Gets the PHP version.
   */
  public function getPhpVersion(): string {
    return $this->data['php_version'];
  }

  /**
   * Gets the PHP version extra part.
   */
  public function getPhpVersionExtra(): ?string {
    return $this->data['php_version_extra'] ?? NULL;
  }

  /**
   * Gets the PHP architecture.
   */
  public function getPhpArchitecture(): int {
    return $this->data['php_architecture'];
  }

  /**
   * Gets the PHP timezone.
   */
  public function getPhpTimezone(): string {
    return $this->data['php_timezone'];
  }

  /**
   * Returns true if the XDebug is enabled.
   */
  public function hasXdebug(): bool {
    return $this->data['xdebug_enabled'];
  }

  /**
   * Returns true if APCu is enabled.
   */
  public function hasApcu(): bool {
    return $this->data['apcu_enabled'];
  }

  /**
   * Returns true if Zend OPcache is enabled.
   */
  public function hasZendOpcache(): bool {
    return $this->data['zend_opcache_enabled'];
  }

  /**
   * Gets the PHP SAPI name.
   */
  public function getSapiName(): string {
    return $this->data['sapi_name'];
  }

  /**
   * Gets the OpenTelemetry TraceId, if any.
   */
  public function getTraceId(): ?string {
    return $this->data['trace_id'] ?? NULL;
  }

  /**
   * Add GIT information to the collected data.
   *
   * @param array $data
   *   The collected data.
   */
  private function addGitInfo(array &$data): void {
    try {
      $process = new Process(
        [
          'git',
          'log',
          '-1',
          '--pretty=format:"%H - %s (%ci)"',
          '--abbrev-commit',
        ],
      );
      $process->setTimeout(3600);
      $process->mustRun();
      $data['git_commit'] = $process->getOutput();

      $process = new Process(
        [
          'git',
          'log',
          '-1',
          '--pretty=format:"%h"',
          '--abbrev-commit',
        ],
      );
      $process->setTimeout(3600);
      $process->mustRun();
      $data['abbr_git_commit'] = $process->getOutput();
    }
    catch (ProcessFailedException | RuntimeException | LogicException $e) {
      $data['git_commit'] = $data['git_commit_abbr'] = NULL;
    }
  }

}
