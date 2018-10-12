<?php

namespace Drupal\dp_reward;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

class ProfitMarketgid implements ProfitInterface, ContainerInjectionInterface {

  private $money;
  private $source_name = 'marketgid';
  private $source_currency = 'USD';

  public function __construct(Money $money) {
    $this->money = $money;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dp_reward.money')
    );
  }

  public function getProfit($startDate = NULL, $endDate = NULL) {

    $data = $this->getInitData();

    $token = $data['token'];
    $authId = $data['idAuth'];


      $urlData = 'http://api.marketgid.com/v1/publishers/' . $authId . '/widgets?token='
      . $token .
      '&dateInterval=interval&startDate='
      . $startDate .
      '&endDate='
      . $endDate;


    $response = $this->money->getResponse($urlData);
    if ($response) {

      $reward_data = json_decode($response, TRUE);

      if (json_last_error() == JSON_ERROR_NONE) {
        $reward_data = reset($reward_data);

        $revenue = 0;

        foreach ($reward_data as $key => $data) {
          $revenue += $data['revenue'];
        }

        $source = $this->money->getFullProfitData(
          $this->source_currency,
          (string)$revenue
        );

        return $source;
      }
    }
    return NULL;
  }

  private function getInitData() {

    $url = 'http://api.marketgid.com/v1/auth/token';
    $parameters = 'email=adv@donpress.com&password=oFNp7wDR';

    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec( $ch );
    $data = json_decode($response, TRUE);

    return $data;
  }
}
