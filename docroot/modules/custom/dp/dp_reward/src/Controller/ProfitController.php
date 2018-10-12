<?php

namespace Drupal\dp_reward\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dp_reward\AggregateData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProfitController extends ControllerBase{

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
  public function content() {

//    return [
//      '#markup' => ''
//    ];

    \Drupal::service('page_cache_kill_switch')->trigger();

    $profit = $this->data->getProfitOutput();

    $build = array(
      'page' => array(
        '#title' => '',
        '#theme' => 'profit_page',
        '#profit' => $profit,
      ),
    );

    $html = \Drupal::service('renderer')->renderRoot($build);
    $response = new Response();
    $response->setContent($html);
    $response->setSharedMaxAge(0);
    $response->setMaxAge(0);

    return $response;

  }

  public function json() {
    $profit = $this->data->getProfitOutput();
    return new JsonResponse($profit);
  }
}