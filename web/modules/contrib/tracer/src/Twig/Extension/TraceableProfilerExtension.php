<?php

declare(strict_types = 1);

namespace Drupal\tracer\Twig\Extension;

use Drupal\tracer\TracerInterface;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

/**
 * Twig extension to trace Twig templates.
 */
class TraceableProfilerExtension extends ProfilerExtension {

  /**
   * Traced events.
   *
   * @var \SplObjectStorage
   */
  private \SplObjectStorage $events;

  /**
   * TraceableProfilerExtension constructor.
   *
   * @param \Twig\Profiler\Profile $profile
   *   The Twig profile.
   * @param \Drupal\tracer\TracerInterface $tracer
   *   The tracer service.
   */
  public function __construct(
    protected readonly Profile $profile,
    protected readonly TracerInterface $tracer
  ) {
    parent::__construct($profile);
    $this->events = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function enter(Profile $profile): void {
    if ($profile->isTemplate()) {
      $this->events[$profile] = $this->tracer->start('Twig', $profile->getName());
    }

    parent::enter($profile);
  }

  /**
   * {@inheritdoc}
   */
  public function leave(Profile $profile): void {
    parent::leave($profile);

    if ($profile->isTemplate()) {
      $this->tracer->stop($this->events[$profile]);
      unset($this->events[$profile]);
    }
  }

}
