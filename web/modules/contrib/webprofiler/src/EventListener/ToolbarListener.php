<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\EventListener;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\webprofiler\Csp\ContentSecurityPolicyHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DumpDataCollector;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listen to kernel response event to inject the toolbar.
 */
class ToolbarListener implements EventSubscriberInterface {

  /**
   * An immutable config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $config;

  /**
   * WebDebugToolbarListener constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
   *   The url generator service.
   * @param \Drupal\webprofiler\Csp\ContentSecurityPolicyHandler $cspHandler
   *   The Content-Security-Policy handler service.
   * @param \Symfony\Component\HttpKernel\DataCollector\DumpDataCollector $dumpDataCollector
   *   The dump data collector.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   */
  public function __construct(
    protected readonly RendererInterface $renderer,
    protected readonly AccountInterface $currentUser,
    protected readonly UrlGeneratorInterface $urlGenerator,
    protected readonly ContentSecurityPolicyHandler $cspHandler,
    protected readonly DumpDataCollector $dumpDataCollector,
    ConfigFactoryInterface $config
  ) {
    $this->config = $config->get('webprofiler.settings');
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse', -128],
    ];
  }

  /**
   * Listen for the kernel.response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   A response event.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    $response = $event->getResponse();
    $request = $event->getRequest();

    if ($response->headers->has('X-Debug-Token') && NULL != $this->urlGenerator) {
      try {
        $response->headers->set(
          'X-Debug-Token-Link',
          $this->urlGenerator->generate('webprofiler.dashboard', ['token' => $response->headers->get('X-Debug-Token')], UrlGeneratorInterface::ABSOLUTE_URL),
        );
      }
      catch (\Exception $e) {
        $response->headers->set('X-Debug-Error', $e::class . ': ' . preg_replace('/\s+/', ' ', $e->getMessage()));
      }
    }

    if (!$event->isMainRequest()) {
      return;
    }

    if ($this->dumpDataCollector->getDumpsCount() > 0) {
      $this->cspHandler->disableCsp();
    }

    $nonces = $this->cspHandler->updateResponseHeaders($request, $response);

    // Do not capture redirects or modify XML HTTP Requests.
    if ($request->isXmlHttpRequest()) {
      return;
    }

    $intercept_redirects = (bool) $this->config->get('intercept_redirects');
    if ($response->headers->has('X-Debug-Token') && $response->isRedirect() && $intercept_redirects && 'html' === $request->getRequestFormat()) {
      $toolbarRedirect = [
        '#theme' => 'webprofiler_toolbar_redirect',
        '#location' => $response->headers->get('Location'),
      ];

      $response->setContent((string) $this->renderer->renderRoot($toolbarRedirect));
      $response->setStatusCode(200);
      $response->headers->remove('Location');
    }

    if (!$response->headers->has('X-Debug-Token')
      || $response->isRedirection()
      || ($response->headers->has('Content-Type') && !str_contains($response->headers->get('Content-Type'), 'html'))
      || 'html' !== $request->getRequestFormat()
      || FALSE !== stripos($response->headers->get('Content-Disposition', ''), 'attachment;')
    ) {
      return;
    }

    if ($this->currentUser->hasPermission('view webprofiler toolbar')) {
      $this->injectToolbar($response, $request, $nonces);
    }
  }

  /**
   * Injects the web debug toolbar into the given Response.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   A response.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request.
   * @param array $nonces
   *   Nonces used in Content-Security-Policy header.
   */
  protected function injectToolbar(Response $response, Request $request, array $nonces): void {
    $content = $response->getContent();
    if (FALSE === $content) {
      return;
    }

    $pos = strripos($content, '</body>');

    if (FALSE !== $pos) {
      $toolbarJs = [
        '#theme' => 'webprofiler_toolbar_js',
        '#token' => $response->headers->get('X-Debug-Token'),
        '#request' => $request,
        '#csp_script_nonce' => $nonces['csp_script_nonce'] ?? NULL,
        '#csp_style_nonce' => $nonces['csp_style_nonce'] ?? NULL,
      ];

      $toolbar = "\n" . str_replace("\n", '', (string) $this->renderer->renderRoot($toolbarJs)) . "\n";
      $content = substr($content, 0, $pos) . $toolbar . substr($content, $pos);
      $response->setContent($content);
    }
  }

}
