<?php

namespace Drupal\dp_reward\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dp_reward\AggregateData;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RewardController extends ControllerBase{

  /**
   * @var AggregateData
   */
  private $data;

  public function __construct(AggregateData $data)
  {
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dp_reward.data')
    );
  }


  /**
   * Display the markup.
   *
   * @return array
   */
  public function content($period) {

    \Drupal::service('page_cache_kill_switch')->trigger();

    $stat = $this->data->getData($period);
    return [
      '#title' => $stat['service']['title'],
      '#theme' => 'stat_page',
      '#common' => $stat['common'],
      '#users' => $stat['users'],
      '#profit' => $stat['profit'],
      '#service' => $stat['service'],
    ];
  }
}