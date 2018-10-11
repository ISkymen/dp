<?php

namespace Drupal\si_stat\Plugin\views\field;

use Drupal\views\Plugin\views\field\NumericField;
use Drupal\Core\Session\AccountInterface;

/**
 * Field handler to display numeric values from the si_stat module.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("stat_numeric")
 */
class StatNumeric extends NumericField {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('view post access counter');
  }

}
