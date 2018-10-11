<?php

namespace Drupal\si_stat\Plugin\views\field;

use Drupal\views\Plugin\views\field\Date;
use Drupal\Core\Session\AccountInterface;

/**
 * Field handler to display the most recent time the node has been viewed.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("si_stat_timestamp")
 */
class NodeCounterTimestamp extends Date {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('view post access counter');
  }

}
