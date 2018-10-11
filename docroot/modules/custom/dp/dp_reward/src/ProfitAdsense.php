<?php

namespace Drupal\dp_reward;

use Google_Service_AdSense;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;


class ProfitAdsense implements ProfitInterface, ContainerInjectionInterface {

  private $money;
  private $source_name = 'adsense';
  private $source_currency = 'EUR';

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

  public function getProfit($startDate=NULL, $endDate=NULL) {

    $config = \Drupal::config('dp_reward.settings');

    $client = \Drupal::service('dp_reward.google_auth')->googleClient();
    $accessToken = \Drupal::service('dp_reward.google_auth')->getToken($client);

    if ($accessToken) {

      $optParams = [
        'metric' => [
          'EARNINGS'
        ],
        'useTimezoneReporting' => TRUE,
        'filter' => [
          'URL_CHANNEL_NAME==' . $config->get('google_url_channel_name')
        ]
      ];

      // Create service.
      $service = new Google_Service_AdSense($client);

      $results = $service->accounts_reports->generate($config->get('google_account_id'), $startDate, $endDate, $optParams);

      $_SESSION['access_token'] = $client->getAccessToken();

      $revenue = $results->totals[0];

      $source = $this->money->getFullProfitData(
        $this->source_currency,
        $revenue
      );

      return $source;
    }

    else {
      return NULL;
    }
  }
}
