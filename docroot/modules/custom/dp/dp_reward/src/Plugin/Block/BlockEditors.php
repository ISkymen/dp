<?php

namespace Drupal\dp_reward\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
* Provides a 'editors' block.
*
* @Block(
*   id = "dp_block_editors",
*   admin_label = @Translation("Editors' stat"),
*
* )
*/
class BlockEditors extends BlockBase {

  public function build() {

    $stat = \Drupal::service('dp_reward.data')->getBlockData();

    return array(
      '#theme' => 'dp_block_editors',

      //  '#title' => 'Websolutions Agency',
      '#users' => $stat['users'],
      '#totalDay' => $stat['totalDay'],
      '#totalMonth' => $stat['totalMonth'],
      '#cache' => array('max-age' => 60)
    );
  }


}