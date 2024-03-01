<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\webprofiler\Profiler\Profiler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profile;

/**
 * Collects frontend navigation and CWV data.
 */
class FrontendController extends ControllerBase {

  /**
   * FrontendController constructor.
   *
   * @param \Drupal\webprofiler\Profiler\Profiler $profiler
   *   The profiler.
   */
  final public function __construct(private readonly Profiler $profiler) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): FrontendController {
    return new static(
      $container->get('webprofiler.profiler'),
    );
  }

  /**
   * Save the navigation data to frontend collector.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
   *   The profile.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function saveNavigationDataAction(Profile $profile, Request $request): JsonResponse {
    $this->profiler->disable();

    $data = Json::decode($request->getContent());

    /** @var \Drupal\webprofiler\DataCollector\FrontendDataCollector $collector */
    $collector = $profile->getCollector('frontend');
    $collector->setNavigationData($data);
    $this->profiler->updateProfile($profile);

    return new JsonResponse(['success' => TRUE]);
  }

  /**
   * Save the Core Web Vitals data to frontend collector.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
   *   The profile.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function saveCwvDataAction(Profile $profile, Request $request): JsonResponse {
    $this->profiler->disable();

    $data = Json::decode($request->getContent());

    /** @var \Drupal\webprofiler\DataCollector\FrontendDataCollector $collector */
    $collector = $profile->getCollector('frontend');
    $collector->setCwvData($data);
    $this->profiler->updateProfile($profile);

    return new JsonResponse(['success' => TRUE]);
  }

}
