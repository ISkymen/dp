<?php

namespace Drupal\dp_reward;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\user\Entity\User;


class AggregateData implements ContainerInjectionInterface{

  private $database;
  private $money;
  private $yandex;
  private $adsense;
  private $marketgid;

  public function __construct(Connection $database, Money $money, ProfitYandex $yandex, ProfitAdsense $adsense, ProfitMarketgid $marketgid)
  {
    $this->database = $database;
    $this->money = $money;
    $this->yandex = $yandex;
    $this->adsense = $adsense;
    $this->marketgid = $marketgid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('dp_reward.money'),
      $container->get('dp_reward.profit.yandex'),
      $container->get('dp_reward.profit.adsense'),
      $container->get('dp_reward.profit.marketgid')
    );
  }


  public function getUserData($period = 'today') {

    $now = \Drupal::time()->getRequestTime();

    if ($period == 'today') {
      $startDate = strtotime('midnight', $now);
      $endDate = $now;
      $period_views = 'day';
    }
    elseif ($period == 'thismonth') {
      $startDate = strtotime('first day of this month 00:00:00', $now);
      $endDate = $now;
      $period_views = 'month';
    }
    elseif ($period == 'thismonthday') {
      $startDate = strtotime('first day of this month 00:00:00', $now);
      $endDate = $now;
      $period_views = 'day';
    }
    elseif ($period == 'lastmonth') {
      $startDate = strtotime('first day of last month 00:00:00', $now);
      $endDate = strtotime('last day of last month 00:00:00', $now);
      $period_views = 'total';
    }

    // Get Quantity of News Entity and month's Views per User
    $queryNews = $this->database->select('news_field_data', 'news');
    $queryNews->fields('news', array('uid'));
    $queryNews->condition('news.created', [$startDate, $endDate], 'BETWEEN');
    $queryNews->groupBy('news.uid');
    $queryNews->leftJoin('si_stat', 'stat', 'stat.entity_id=news.id');
    $queryNews->condition('stat.entity_type', 'news');
    $queryNews->addExpression('COUNT(news.id)', 'quantity');
    $queryNews->addExpression('SUM(stat.' . $period_views . 'count)', 'views');
    $resultNews = $queryNews->execute()->fetchAllAssoc('uid', \PDO::FETCH_ASSOC);

    // Get Quantity of Node Entity and month's Views per User
    $queryNode = $this->database->select('node_field_data', 'node');
    $queryNode->fields('node', array('uid'));
    $queryNode->condition('node.created', [$startDate, $endDate], 'BETWEEN');
    $queryNode->groupBy('node.uid');
    $queryNode->leftJoin('si_stat', 'stat', 'stat.entity_id=node.nid');
    $queryNode->condition('stat.entity_type', 'node');
    $queryNode->addExpression('COUNT(node.nid)', 'quantity');
    $queryNode->addExpression('SUM(stat.' . $period_views . 'count)', 'views');
    $resultNode = $queryNode->execute()->fetchAllAssoc('uid', \PDO::FETCH_ASSOC);

    // Merge and sum Node and News stats per User
    $userData = array();
    $uids = array_keys($resultNews + $resultNode);
    $users = User::loadMultiple($uids);
    $config = \Drupal::config('dp_reward.settings');
    foreach ($uids as $key) {
      $userData[$key]['views'] = (isset($resultNews[$key]['views']) ? $resultNews[$key]['views'] : 0)
        + (isset($resultNode[$key]['views']) ? $resultNode[$key]['views'] : 0);
      $userData[$key]['quantity'] = (isset($resultNews[$key]['quantity']) ? $resultNews[$key]['quantity'] : 0)
        + (isset($resultNode[$key]['quantity']) ? $resultNode[$key]['quantity'] : 0);
      $userData[$key]['name'] = $users[$key]->getDisplayName();
      $userData[$key]['user_path'] = \Drupal::service('path.alias_manager')->getAliasByPath('/user/' . $key);
      if ($period_views == 'day') {
        $access = $users[$key]->getLastAccessedTime();
        $userData[$key]['access'] = date("d.m.Y H:i", $access);
        $userData[$key]['active'] = ($access < ($now - $config->get('active_timeout')*86400)) ? FALSE : TRUE;
        $userData[$key]['status'] = ($access > ($now - $config->get('status_timeout')*60)) ? 'online' : 'offline';
      }
    }

    return $userData;
  }

  public function getTotalStats($userData) {

    $totalStats = array();
    $totalStats['totalQuantity'] = 0;
    $totalStats['totalViews'] = 0;

    foreach ($userData as $key => $stat) {
      $totalStats['totalQuantity'] += $stat['quantity'];
      $totalStats['totalViews'] += $stat['views'];
    }

    return $totalStats;
  }

  public function getDays($startDate, $endDate) {
    return round((strtotime($endDate) - strtotime($startDate)) / 86400 + 1);
  }

  public function getData($period) {

    $time_start = microtime(TRUE);

    $config = \Drupal::config('dp_reward.settings');

    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    if (in_array('administrator', $roles) || in_array('owner', $roles)) {
      $stat['service']['owner'] = TRUE;
    }

    $now = \Drupal::time()->getRequestTime();

    if ($period == 'today') {
      $endDate = $startDate = date('Y-m-d', $now);
    }
    elseif ($period == 'thismonth') {
      $startDate = date('Y-m-01', $now);
      $endDate = date('Y-m-d', $now);
    }
    elseif ($period == 'lastmonth') {
      $startDate = date("Y-m-d", strtotime("first day of previous month"));
      $endDate = date("Y-m-d", strtotime("last day of previous month"));
    }

    $stat['service']['title'] = t('Статистика за период: ') . $startDate . ' — ' . $endDate;

    $totalDays = $this->getDays($startDate, $endDate);
    
    $userData = $this->getUserData($period);
    $totalStats = $this->getTotalStats($userData);

    $stat['common']['quantity'] = $totalStats['totalQuantity'];
    $stat['common']['views'] = $totalStats['totalViews'];
    $stat['profit'] = $this->totalProfitRange($period);

    $stat['common']['rewardEditors'] = round($stat['profit']['total_uah'] * $config->get('editors_ratio'), 2);
    $stat['service']['rewardOwners'] = round($stat['profit']['total_uah'] - $stat['common']['rewardEditors'], 2);
    $stat['service']['rewardOwner'] = round($stat['service']['rewardOwners']/3, 2);
    $stat['common']['rewardViews'] = round($stat['common']['rewardEditors'] * $config->get('views_ratio'), 2);
    $stat['common']['rewardQuantity'] = round($stat['common']['rewardEditors'] - $stat['common']['rewardViews'], 2);
    $stat['common']['rewardPerQuantity'] = round((($stat['common']['quantity'] === 0)
      ? '0' : $stat['common']['rewardQuantity'] / $stat['common']['quantity']), 2);
    $stat['common']['rewardPerViews'] = round((($stat['common']['views'] === 0)
      ? '0' : $stat['common']['rewardViews'] / $stat['common']['views']) * 1000, 2);
    $stat['common']['averageViews'] = ($stat['common']['quantity'] !=0 )
      ? round($stat['common']['views'] / $stat['common']['quantity'])
      : 0;
    $stat['common']['averageQuantity'] = round($stat['common']['quantity'] / $totalDays);


    foreach ($userData as $key => &$user) {
      $user['averageViews'] = ($user['quantity'] != 0) ? round($user['views'] / $user['quantity']) : 0;
      $user['averageQuantity'] = round($user['quantity'] / $totalDays);
      $user['rewardQuantity'] = $user['quantity'] * $stat['common']['rewardPerQuantity'];
      $user['rewardViews'] = round($user['views'] * $stat['common']['rewardPerViews'] / 1000, 2);
      $user['rewardTotal'] = $user['rewardQuantity'] + $user['rewardViews'];
    }

    $stat['users'] = $userData;

    $time_end = microtime(TRUE);
    $runtime = round($time_end - $time_start, 4);

    $stat['service']['runtime'] = $runtime;

    return $stat;
  }

  public function  getTotalProfitRange($period) {

    $now = \Drupal::time()->getRequestTime();
    if ($period == 'today') {
      $endDate = $startDate = date('Y-m-d', $now);
    }
    if ($period == 'thismonth') {
      $startDate = date('Y-m-01', $now);
      $endDate = date('Y-m-d', $now);
    }
    elseif ($period == 'lastmonth') {
      $startDate = date("Y-m-d", strtotime("first day of previous month"));
      $endDate = date("Y-m-d", strtotime("last day of previous month"));
    }

    $source_config = 'dp_reward.profit_' . $period;
    $profit = \Drupal::state()->get($source_config);
    $config = \Drupal::config('dp_reward.settings');
    $interval = $config->get('profit_timeout');

    if ($profit['profit_date'] < ($now - $interval*60)) {

      $profit['total_uah'] = 0;

      $profit['source']['yandex'] = $this->yandex->getProfit($startDate, $endDate);
      $profit['source']['adsense'] = $this->adsense->getProfit($startDate, $endDate);
      $profit['source']['marketgid'] = $this->marketgid->getProfit($startDate, $endDate);

      foreach ($profit['source'] as $key => $value) {
        $profit['total_uah'] += $value['profit_uah'];
      }

      $profit['profit_date'] = $now;
      \Drupal::state()->set($source_config, $profit);
    }
    return $profit;
  }

  public function  totalProfitRange($period) {
    $config = \Drupal::config('dp_reward.settings');
    $cron = $config->get('cron');
    if ($cron) {
      $source_config = 'dp_reward.profit_' . $period;
      $profit = \Drupal::state()->get($source_config);
      return $profit;
    }
    else {
      return $this->getTotalProfitRange($period);
    }
  }

  public function getProfitOutput() {

    $profit = array();

    $profit_month = $this->totalProfitRange('thismonth');
    $profit_today = $this->totalProfitRange('today');

    $profit['today_total_uah'] = (string)round($profit_today['total_uah'], 2);
    $profit['month_total_uah'] = (string)round($profit_month['total_uah'], 2);
    $profit['profit_date'] = (string)($profit_month['profit_date']);

    foreach ($profit_month['source']  as $key => &$value) {
      $profit['sources'][] = [
        'source' => $key,
        'currency' => (string)$value['currency'],
        'today_profit' => (string)$profit_today['source'][$key]['profit'],
        'today_profit_uah' => (string)$profit_today['source'][$key]['profit_uah'],
        'today_percent' => ($profit['today_total_uah'])
          ?(string)round($profit_today['source'][$key]['profit_uah']/ $profit['today_total_uah']*100, 2)
          : "0",
        'month_profit' => (string)$value['profit'],
        'month_profit_uah' => (string)$value['profit_uah'],
        'month_percent' => ($profit['month_total_uah'])
          ? (string)round($value['profit_uah']/ $profit['month_total_uah']*100, 2)
          : "0",
      ];
    }
    return $profit;
  }

  public function getBlockData() {

    $time_start = microtime(TRUE);

    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    if (in_array('administrator', $roles) || in_array('owner', $roles)) {
      $stat['service']['owner'] = TRUE;
    }

    $userDay = $this->getUserData('today');
    $userMonth = $this->getUserData('thismonthday');

    $userData = array();
    $uids = array_keys($userDay + $userMonth);

    foreach ($uids as $key) {
      $userData[$key]['quantityDay'] = (isset($userDay[$key]['quantity']) ? $userDay[$key]['quantity'] : 0);
      $userData[$key]['viewsDay'] = (isset($userDay[$key]['views']) ? $userDay[$key]['views'] : 0);
      $userData[$key]['quantityMonth'] = (isset($userMonth[$key]['quantity']) ? $userMonth[$key]['quantity'] : 0);
      $userData[$key]['viewsMonth'] = (isset($userMonth[$key]['views']) ? $userMonth[$key]['views'] : 0);
      $userData[$key]['name'] = (isset($userMonth[$key]['name']) ? $userMonth[$key]['name'] : $key);
      $userData[$key]['user_path'] = (isset($userMonth[$key]['user_path']) ? $userMonth[$key]['user_path'] : $key);
      $userData[$key]['status'] = (isset($userMonth[$key]['status']) ? $userMonth[$key]['status'] : NULL);
      $userData[$key]['active'] = (isset($userMonth[$key]['active']) ? $userMonth[$key]['active'] : NULL);
      $userData[$key]['access'] = (isset($userMonth[$key]['access']) ? $userMonth[$key]['access'] : NULL);
    }

    $totalDay = $this->getTotalStats($userDay);
    $totalMonth = $this->getTotalStats($userMonth);

    $stat['users'] = $userData;
    $stat['totalDay'] = $totalDay;
    $stat['totalMonth'] = $totalMonth;


    $time_end = microtime(TRUE);
    $runtime = round($time_end - $time_start, 4);

    $stat['service']['runtime'] = $runtime;

    return $stat;
  }

}