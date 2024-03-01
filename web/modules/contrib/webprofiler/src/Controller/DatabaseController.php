<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Controller for the database panel actions.
 */
class DatabaseController extends ControllerBase {

  /**
   * The Profiler service.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private Profiler $profiler;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private Connection $database;

  /**
   * DatabaseController constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   *   The Profiler service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  final public function __construct(Profiler $profiler, Connection $database) {
    $this->profiler = $profiler;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): DatabaseController {
    return new static(
      $container->get('webprofiler.profiler'),
      $container->get('database'),
    );
  }

  /**
   * Render the explain table for the given query.
   *
   * @param string $token
   *   A profile token.
   * @param int $qid
   *   The query id.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   A table with the query explain results.
   */
  public function explainAction(string $token, int $qid): AjaxResponse {
    $profile = $this->profiler->loadProfile($token);

    if ($profile == NULL) {
      return new AjaxResponse('');
    }

    $query = $this->getQuery($profile, $qid);

    $result = $this
      ->database
      ->query('EXPLAIN ' . $query['query'], (array) $query['args'])
      ->fetchAll();

    $header = [];
    $rows = [];
    foreach ($result as $row) {
      $header = [];
      $table_row = [];
      foreach ($row as $key => $value) {
        $header[] = $key;
        $table_row[] = $value;
      }
      $rows[] = $table_row;
    }

    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.js--explain-target-' . $qid,
        [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ],
      ),
    );

    return $response;
  }

  /**
   * Load a query from a profile.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
   *   The profile.
   * @param int $qid
   *   The query id.
   *
   * @return array
   *   A loaded query.
   */
  private function getQuery(Profile $profile, int $qid): array {
    $this->profiler->disable();
    $token = $profile->getToken();

    $profile = $this->profiler->loadProfile($token);

    if ($profile == NULL) {
      throw new NotFoundHttpException(sprintf('Token %s does not exist.', $token));
    }

    /** @var \Drupal\webprofiler\DataCollector\DatabaseDataCollector $databaseCollector */
    $databaseCollector = $profile->getCollector('database');

    $queries = $databaseCollector->getQueries();

    return $queries[$qid];
  }

}
