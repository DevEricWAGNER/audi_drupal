<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Profiler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpKernel\Profiler\FileProfilerStorage;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\Profiler as SymfonyProfiler;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

/**
 * Extend the Symfony profiler to allow to choose the list of collectors.
 */
class Profiler extends SymfonyProfiler {

  /**
   * The profiler storage.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface
   */
  private ProfilerStorageInterface $localStorage;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface|null
   */
  private ?LoggerInterface $localLogger;

  /**
   * Profiler constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\FileProfilerStorage $storage
   *   The profiler storage.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   */
  public function __construct(FileProfilerStorage $storage, LoggerInterface $logger, private readonly ConfigFactoryInterface $config) {
    parent::__construct($storage, $logger);

    $this->localStorage = $storage;
    $this->localLogger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function add(DataCollectorInterface $collector): void {
    $activeToolbarItems = $this
      ->config
      ->get('webprofiler.settings')
      ->get('active_toolbar_items');

    // Drupal collector should not be disabled.
    if ($collector->getName() == 'drupal') {
      parent::add($collector);
    }
    else {
      if (is_array($activeToolbarItems) && array_key_exists($collector->getName(), $activeToolbarItems) && $activeToolbarItems[$collector->getName()] !== '0') {
        parent::add($collector);
      }
    }
  }

  /**
   * Update the profile with new data.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
   *   The profile with new data.
   *
   * @return bool
   *   True if the profile was updated successfully.
   */
  public function updateProfile(Profile $profile): bool {
    if (!($ret = $this->localStorage->write($profile)) && NULL !== $this->localLogger) {
      $this->localLogger->warning('Unable to store the profiler information.');
    }

    return $ret;
  }

}
