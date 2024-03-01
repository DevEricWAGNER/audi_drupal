<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * ProfilerListener collects data for the current request.
 */
class ProfilerListener implements EventSubscriberInterface {

  /**
   * The profiler.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private Profiler $profiler;

  /**
   * The request matcher.
   *
   * @var \Symfony\Component\HttpFoundation\RequestMatcherInterface|null
   */
  private ?RequestMatcherInterface $matcher;

  /**
   * The Throwable exception.
   *
   * @var \Throwable|null
   */
  private ?\Throwable $exception = NULL;

  /**
   * Collected profiles.
   *
   * @var \SplObjectStorage
   */
  private \SplObjectStorage $profiles;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private RequestStack $requestStack;

  /**
   * Store the parents of the current request.
   *
   * @var \SplObjectStorage
   */
  private \SplObjectStorage $parents;

  /**
   * ProfilerListener constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   *   The profiler.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface|null $matcher
   *   The request matcher.
   */
  public function __construct(Profiler $profiler, RequestStack $requestStack, RequestMatcherInterface $matcher = NULL) {
    $this->profiler = $profiler;
    $this->matcher = $matcher;
    $this->profiles = new \SplObjectStorage();
    $this->parents = new \SplObjectStorage();
    $this->requestStack = $requestStack;
  }

  /**
   * Handles the onKernelException event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   The event to process.
   */
  public function onKernelException(ExceptionEvent $event): void {
    $this->exception = $event->getThrowable();
  }

  /**
   * Handles the onKernelResponse event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    $request = $event->getRequest();

    $exception = $this->exception;
    $this->exception = NULL;

    if (NULL !== $this->matcher && !$this->matcher->matches($request)) {
      return;
    }

    $session = $request->hasPreviousSession() && $request->hasSession() ? $request->getSession() : NULL;

    if ($session instanceof Session) {
      $usageIndexValue = $usageIndexReference = &$session->getUsageIndex();
      $usageIndexReference = \PHP_INT_MIN;
    }

    try {
      $profile = $this->profiler->collect($request, $event->getResponse(), $exception);
      if ($profile == NULL) {
        return;
      }
    }
    finally {
      if ($session instanceof Session) {
        $usageIndexReference = $usageIndexValue;
      }
    }

    // Alter a BigPipe request to collect it independently. This is needed
    // because when BigPipe is enabled, the system receives a single request,
    // but it returns (possibly) multiple responses.
    $response = $event->getResponse();
    if ($response->headers->has('X-Drupal-BigPipe-Placeholder')) {
      $request = $request->duplicate(
        NULL,
        NULL,
        array_merge(
          $request->attributes->all(),
          ['big_pipe' => $response->headers->get('X-Drupal-BigPipe-Placeholder')],
        ),
      );
    }

    $this->profiles[$request] = $profile;

    $this->parents[$request] = $this->requestStack->getParentRequest();
  }

  /**
   * Handles the onKernelTerminate event.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   The event to process.
   */
  public function onKernelTerminate(TerminateEvent $event): void {
    // Attach children to parents.
    foreach ($this->profiles as $request) {
      if (NULL !== $parentRequest = $this->parents[$request]) {
        if (isset($this->profiles[$parentRequest])) {
          $this->profiles[$parentRequest]->addChild($this->profiles[$request]);
        }
      }
    }

    // Save profiles.
    foreach ($this->profiles as $request) {
      $this->profiler->saveProfile($this->profiles[$request]);
    }

    $this->profiles = new \SplObjectStorage();
    $this->parents = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse', -100],
      KernelEvents::EXCEPTION => ['onKernelException', 0],
      KernelEvents::TERMINATE => ['onKernelTerminate', -1024],
    ];
  }

}
