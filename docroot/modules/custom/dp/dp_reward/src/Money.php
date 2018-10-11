<?php

namespace Drupal\dp_reward;

class Money {

  public function getExchangeRates() {

    // $interval - Timeout for getting data by API in hours

    $now = \Drupal::time()->getRequestTime();
    $exchange = \Drupal::state()->get('dp_reward.exchange_rates');
    $config = \Drupal::config('dp_reward.settings');
    $interval = $config->get('exchange_timeout');

    if ($exchange['date'] < ($now - $interval*3600)) {

      $urlData = 'https://api.privatbank.ua/p24api/pubinfo?exchange&json&coursid=11';
      $exchangeRates = array();

      $response = $this->getResponse($urlData);

      if ($response) {

        $exchangeRatesData = json_decode($response, TRUE);

        if (json_last_error() == JSON_ERROR_NONE) {

          foreach ($exchangeRatesData as $item) {
            $exchangeRates[$item['ccy']] = $item['buy'];
          }

          $exchange['rates'] = $exchangeRates;
          $exchange['date'] = $now;

          \Drupal::state()->set('dp_reward.exchange_rates', $exchange);
        }
      }
    }

    return $exchange;
  }

  public function getExchangeRate($currency) {
    $exchange = $this->getExchangeRates();
    $exchangeRate['date'] = $exchange['date'];
    $exchangeRate['rate'] = $exchange['rates'][$currency];
    return $exchangeRate;
  }

  public function getResponse($url, $timeout = 5) {
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $response=curl_exec($ch);
    curl_close($ch);
    return $response;
  }


  public function getFullProfitData($source_currency, $profit) {
      $source['currency'] = $source_currency;
      $source['profit'] = ($profit) ? $profit : 0;
      $exchangeRate = $this->getExchangeRate($source['currency']);
      $source['profit_uah'] = round($source['profit'] * $exchangeRate['rate'], 2);
      $source['exchange_rate'] = round($exchangeRate['rate'], 2);
      $source['exchange_date'] = $exchangeRate['date'];
      return $source;
  }
}