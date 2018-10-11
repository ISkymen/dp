<?php

namespace Drupal\dp_reward;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

class ProfitYandex implements ProfitInterface, ContainerInjectionInterface {

  private $money;
  private $source_name = 'yandex';
  private $source_currency = 'RUR';

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

    $config = \Drupal::config('dp_reward.settings');

    $token = 'AQAAAAAEv-h3AAMP99Cf6jwRXUtQqugWSNrx5AM'; // @TODO: Move to settings
    // Get token url: https://oauth.yandex.ru/authorize?response_type=token&client_id=05cd61fbe31947159f3665fbf8fc6149

    if ($config->get('yandex_json_url')) {
      $urlData = $config->get('yandex_json_url');
    }
    else {
      $urlData = 'https://partner2.yandex.ru/api/statistics/get.json?oauth_token='
        . $token .
        '&lang=ru&level=advnet_context_on_site&field=partner_wo_nds&period='
        . $startDate .
        '&period='
        . $endDate .
        '&filter=["page_id","=","142723"]';
    }

    $response = $this->money->getResponse($urlData);
    if ($response) {

      $yandex_reward_data = json_decode($response, TRUE);

      if (json_last_error() == JSON_ERROR_NONE) {

        if ($yandex_reward_data['result'] == 'ok') {

          $source = $this->money->getFullProfitData(
            $this->source_currency,
            $yandex_reward_data['data']['data'][0]['partner_wo_nds']
          );

          return $source;
          }
      }
    }
    return NULL;
  }
}
