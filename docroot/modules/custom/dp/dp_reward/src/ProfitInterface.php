<?php

namespace Drupal\dp_reward;

interface ProfitInterface {

  /**
   * Returns the number of times entities have been viewed.
   *
   * @param string $period
   *   An string of period or report.
   *
   * @return \Drupal\si_stat\StatViewsResult[]
   *   An array of value objects representing the number of times each entity
   *   has been viewed. The array is keyed by entity ID. If an ID does not
   *   exist, it will not be present in the array.
   */
  public function getProfit($startDate, $endDate);

}