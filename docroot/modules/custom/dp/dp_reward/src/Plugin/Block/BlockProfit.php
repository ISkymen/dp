<?php

namespace Drupal\dp_reward\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
* Provides a 'profit' block.
*
* @Block(
*   id = "dp_block_profit",
*   admin_label = @Translation("Total profit"),
*
* )
*/
class BlockProfit extends BlockBase {

  public function build() {

    $profit = \Drupal::service('dp_reward.data')->getProfitOutput();

    return array(
      '#theme' => 'dp_block_profit',
      '#profit' => $profit,
      '#cache' => array('max-age' => 60)
    );
  }


}