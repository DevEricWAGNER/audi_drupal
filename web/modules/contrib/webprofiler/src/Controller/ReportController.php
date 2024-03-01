<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\webprofiler\Profiler\Profiler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the report page.
 */
class ReportController extends ControllerBase {

  /**
   * ReportController constructor.
   *
   * @param \Drupal\webprofiler\Profiler\Profiler $profiler
   *   The Profiler service.
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   The Date formatter service.
   */
  final public function __construct(
    private readonly Profiler $profiler,
    private readonly DateFormatter $dateFormatter
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ReportController {
    return new static(
      $container->get('webprofiler.profiler'),
      $container->get('date.formatter'),
    );
  }

  /**
   * Generates the list page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object.
   *
   * @return array
   *   A render array for the profile list table.
   */
  public function list(Request $request): array {
    $this->profiler->disable();

    $ip = $request->query->get('ip');
    $url = $request->query->get('url');
    $limit = intval($request->get('limit', 10));
    $method = $request->query->get('method');
    $method = $method != '- any -' ? $method : NULL;

    $profiles = $this->profiler->find($ip, $url, $limit, $method, '', '');

    $rows = [];
    if (count($profiles) > 0) {
      foreach ($profiles as $profile) {
        $row = [];
        $row[] = Link::fromTextAndUrl($profile['token'], new Url('webprofiler.dashboard', ['token' => $profile['token']]))
          ->toString();
        $row[] = $profile['parent'] != $profile['token'] ? 'Yes' : 'No';
        $row[] = $profile['ip'];
        $row[] = $profile['method'];
        $row[] = $profile['url'];
        $row[] = $this->dateFormatter->format($profile['time']);

        $rows[] = $row;
      }
    }
    else {
      $rows[] = [
        [
          'data' => $this->t('No profiles found'),
          'colspan' => 6,
        ],
      ];
    }

    $build = [];
    $build['filters'] = $this->formBuilder()
      ->getForm('Drupal\\webprofiler\\Form\\ReportFilterForm');

    $build['table'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => [
        $this->t('Token'),
        $this->t('Subrequest'),
        [
          'data' => $this->t('Ip'),
          'class' => [RESPONSIVE_PRIORITY_LOW],
        ],
        [
          'data' => $this->t('Method'),
          'class' => [RESPONSIVE_PRIORITY_LOW],
        ],
        $this->t('Url'),
        [
          'data' => $this->t('Time'),
          'class' => [RESPONSIVE_PRIORITY_MEDIUM],
        ],
      ],
      '#sticky' => TRUE,
    ];

    return $build;
  }

}
