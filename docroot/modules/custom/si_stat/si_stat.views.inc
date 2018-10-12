<?php

/**
 * @file
 * Provide views data for si_stat.module.
 */

/**
 * Implements hook_views_data().
 */
function si_stat_views_data() {
  $data['si_stat']['table']['group']  = t('Content si_stat');

  $data['si_stat']['table']['join'] = [
    'news_field_data' => [
      'left_field' => 'id',
      'field' => 'entity_id',
      'extra' => [
        ['field' => 'entity_type', 'value' => 'news'],
      ],
    ],
  ];

  $data['si_stat']['totalcount'] = [
    'title' => t('Total views'),
    'help' => t('The total number of times the node has been viewed.'),
    'field' => [
      'id' => 'stat_numeric',
      'click sortable' => TRUE,
     ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['si_stat']['yearcount'] = [
    'title' => t('Views this year'),
    'help' => t('The total number of times the node has been viewed this year.'),
    'field' => [
      'id' => 'stat_numeric',
      'click sortable' => TRUE,
     ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];


  $data['si_stat']['monthcount'] = [
    'title' => t('Views this month'),
    'help' => t('The total number of times the node has been viewed this month.'),
    'field' => [
      'id' => 'stat_numeric',
      'click sortable' => TRUE,
     ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];



  $data['si_stat']['weekcount'] = [
    'title' => t('Views this week'),
    'help' => t('The total number of times the node has been viewed this week.'),
    'field' => [
      'id' => 'stat_numeric',
      'click sortable' => TRUE,
     ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['si_stat']['daycount'] = [
    'title' => t('Views today'),
    'help' => t('The total number of times the node has been viewed today.'),
    'field' => [
      'id' => 'stat_numeric',
      'click sortable' => TRUE,
     ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['si_stat']['timestamp'] = [
    'title' => t('Most recent view'),
    'help' => t('The most recent time the node has been viewed.'),
    'field' => [
      'id' => 'si_stat_timestamp',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'date',
    ],
    'argument' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  return $data;
}
