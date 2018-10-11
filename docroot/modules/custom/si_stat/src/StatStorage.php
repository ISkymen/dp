<?php

namespace Drupal\si_stat;

use Drupal\Core\Cache\NullBackend;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the default database storage backend for si_stat.
 */
class StatStorage implements StatStorageInterface {

  /**
  * The database connection used.
  *
  * @var \Drupal\Core\Database\Connection
  */
  protected $connection;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs the si_stat storage.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection for the node view storage.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(Connection $connection, StateInterface $state, RequestStack $request_stack) {
    $this->connection = $connection;
    $this->state = $state;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function recordView($entity_type, $entity_id) {
    return (bool) $this->connection
      ->merge('si_stat')
      //->key('id')
      ->key(['entity_type', 'entity_id'], [$entity_type, $entity_id])
      ->fields([
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'totalcount' => 1,
        'yearcount' => 1,
        'monthcount' => 1,
        'weekcount' => 1,
        'daycount' => 1,
        'timestamp' => $this->getRequestTime(),
      ])
      ->expression('totalcount', 'totalcount + 1')
      ->expression('yearcount', 'yearcount + 1')
      ->expression('monthcount', 'monthcount + 1')
      ->expression('weekcount', 'weekcount + 1')
      ->expression('daycount', 'daycount + 1')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function fetchViews($entity_type, $entity_ids) {
    $query = $this->connection
      ->select('si_stat', 's')
      ->fields('s', [
        'entity_type',
        'entity_id',
        'totalcount',
        'yearcount',
        'monthcount',
        'weekcount',
        'daycount',
        'timestamp'])
      ->condition('entity_type', $entity_type)
      ->condition('entity_id', $entity_ids, 'IN')
      ->execute()
      ->fetchAll();
    if ($query) {
      foreach ($query as $id => $view) {
        $views[$entity_type][$view->entity_id] = new StatViewsResult(
          $view->totalcount,
          $view->yearcount,
          $view->monthcount,
          $view->weekcount,
          $view->daycount,
          $view->timestamp);
      }
      return $views;
    }
    else return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchView($entity_type, $entity_id) {
    $views = $this->fetchViews($entity_type, array($entity_id));
    if ($views) {
      $views = reset($views);
      $views = reset($views);
      return $views;
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAll($order = 'totalcount', $limit = 5) {
    assert(in_array($order, [
      'entity_type',
      'entity_id',
      'totalcount',
      'yearcount',
      'monthcount',
      'weekcount',
      'daycount',
      'timestamp']), "Invalid order argument.");

    return $this->connection
      ->select('si_stat', 's')
      ->fields('s', ['id'])
      ->orderBy($order, 'DESC')
      ->range(0, $limit)
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteViews($entity_type, $entity_id) {
    return (bool) $this->connection
      ->delete('si_stat')
      ->condition('entity_type', $entity_type)
      ->condition('entity_id', $entity_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function resetDayCount() {
    $stat_timestamp = $this->state->get('si_stat.day_timestamp') ?: 0;
    if (($this->getRequestTime() - $stat_timestamp) >= 86400) {
      $this->state->set('si_stat.day_timestamp', $this->getRequestTime());
      $this->connection->update('si_stat')
        ->fields(['daycount' => 0])
        ->execute();
    }
  }
  /**
   * {@inheritdoc}
   */
  public function resetCount() {
    $stat_timestamp = $this->state->get('si_stat.day_timestamp') ?: 0;
    $current_date = $this->getRequestTime();

//    $stat_timestamp -=864000;

    $day = date('j', $current_date) | 0;
    $last_day = date('j', $stat_timestamp) | 0;

    if ($day != $last_day) {

      $this->state->set('si_stat.day_timestamp', $current_date);

      $year = date('Y', $current_date) | 0;
      $month = date('n', $current_date) | 0;
      $week = date('W', $current_date) | 0;

      $last_year = date('Y', $stat_timestamp) | 0;
      $last_month = date('n', $stat_timestamp) | 0;
      $last_week = date('W', $stat_timestamp) | 0;

      $fields = array();

      if ($year != $last_year) {
        $fields['yearcount'] = 0;
      }

      if ($month != $last_month) {
        $fields['monthcount'] = 0;
      }

      if ($week != $last_week) {
        $fields['weekcount'] = 0;
      }

      $fields['daycount'] = 0;

      if (!empty($fields)) {
        $this->connection->update('si_stat')
          ->fields($fields)
          ->execute();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function maxTotalCount() {
    $query = $this->connection->select('si_stat', 's');
    $query->addExpression('MAX(totalcount)');
    $max_total_count = (int)$query->execute()->fetchField();
    return $max_total_count;
  }

  /**
   * Get current request time.
   *
   * @return int
   *   Unix timestamp for current server request time.
   */
  protected function getRequestTime() {
    return $this->requestStack->getCurrentRequest()->server->get('REQUEST_TIME');
  }

}
