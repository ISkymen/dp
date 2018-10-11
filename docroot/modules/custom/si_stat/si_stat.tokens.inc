<?php

/**
 * @file
 * Builds placeholder replacement tokens for node visitor si_stat.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function si_stat_token_info() {
  $node['total-count'] = [
    'name' => t("Total number of views"),
    'description' => t("The total number of visitors who have read the node."),
  ];
  $node['year-count'] = [
    'name' => t("Views this year"),
    'description' => t("The number of visitors who have read the node this year."),
  ];
  $node['month-count'] = [
    'name' => t("Views this month"),
    'description' => t("The number of visitors who have read the node this month."),
  ];
  $node['week-count'] = [
    'name' => t("Views this week"),
    'description' => t("The number of visitors who have read the node this week."),
  ];
  $node['day-count'] = [
    'name' => t("Views today"),
    'description' => t("The number of visitors who have read the node today."),
  ];
  $node['last-view'] = [
    'name' => t("Last view"),
    'description' => t("The date on which a visitor last read the node."),
    'type' => 'date',
  ];

  return [
    'tokens' => ['node' => $node],
  ];
}

/**
 * Implements hook_tokens().
 */
function si_stat_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  $replacements = [];

  if ($type == 'node' & !empty($data['node'])) {
    $node = $data['node'];

    foreach ($tokens as $name => $original) {
      if ($name == 'total-count') {
        $si_stat = si_stat_get($node->id());
        $replacements[$original] = $si_stat['totalcount'];
      }
      elseif ($name == 'year-count') {
        $si_stat = si_stat_get($node->id());
        $replacements[$original] = $si_stat['yearcount'];
      }
      elseif ($name == 'month-count') {
        $si_stat = si_stat_get($node->id());
        $replacements[$original] = $si_stat['monthcount'];
      }
      elseif ($name == 'week-count') {
        $si_stat = si_stat_get($node->id());
        $replacements[$original] = $si_stat['weekcount'];
      }
      elseif ($name == 'day-count') {
        $si_stat = si_stat_get($node->id());
        $replacements[$original] = $si_stat['daycount'];
      }
      elseif ($name == 'last-view') {
        $si_stat = si_stat_get($node->id());
        $replacements[$original] = format_date($si_stat['timestamp']);
      }
    }

    if ($created_tokens = $token_service->findWithPrefix($tokens, 'last-view')) {
      $si_stat = si_stat_get($node->id());
      $replacements += $token_service->generate('date', $created_tokens, ['date' => $si_stat['timestamp']], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
