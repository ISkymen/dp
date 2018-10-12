<?php

namespace Drupal\dp_vars\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;
/**
* Provides a Carousel block.
*
* @Block(
*   id = "dp_block_topics",
*   admin_label = @Translation("Topics"),
*
* )
*/
class TopicsCarousel extends BlockBase {

  public function build() {

    $topics = \Drupal::service('dp_news.data')->getTopics();

    $view = Views::getView('topics_carousel');
    if (is_object($view)) {
      $view->setArguments(array(implode(',',$topics)));
      $view->setDisplay('block_1');
      $view->preExecute();
      $view->execute();
      $content = $view->render();
    }
    return $content;
  }
}