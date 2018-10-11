<?php

namespace Drupal\dp_news;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\user\Entity\User;
use Drupal\dp_news\Entity\News;


class DataQuery implements ContainerInjectionInterface{

  private $database;

  public function __construct(Connection $database)
  {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function getSimilarNews($id, $limit) {
    $news =  News::load($id);
    $terms = $news->get('tags')->getValue();
    $rubric = ($news->get('rubric')) ? $news->get('rubric')->target_id : NULL ;
    $terms = array_column($terms, 'target_id');

    if ($rubric) {
      $newsWithTerm = \Drupal::entityQueryAggregate('news')
        ->condition('tags', $terms, 'IN')
        ->condition('rubric', $rubric) // @TODO How I can dry code for rubric check?
        ->condition('id', $id, '<>')
        ->aggregate('id', 'COUNT')
        ->groupBy('id')
        ->execute();
    }
    else {
      $newsWithTerm = \Drupal::entityQueryAggregate('news')
        ->condition('tags', $terms, 'IN')
        ->condition('id', $id, '<>')
        ->aggregate('id', 'COUNT')
        ->groupBy('id')
        ->execute();
    }

    if ($newsWithTerm) {
      foreach ($newsWithTerm as $key => $row) {
        $similarity[$key]  = $row['id_count'];
        $news_id[$key]  = $row['id'];
      }
      array_multisort($similarity, SORT_DESC, $news_id,SORT_DESC, $newsWithTerm);

      $news_ids = array_column($newsWithTerm, 'id');
      $news_limit = array_slice($news_ids, 0, $limit);
    }
    else {
      $news_limit = NULL;
    }
    return $news_limit;
  }

  public function getTopics() {
    $config = \Drupal::config('dp_vars.settings');
    $days = $config->get('days');
    $now = \Drupal::time()->getRequestTime();
    $last_date = $now - $days*86400;
    $queryTopics= $this->database->select('news_field_data', 'news');
    $queryTopics->fields('news', array('topic'));
    $queryTopics->condition('news.created', $last_date, '>');
    $queryTopics->condition('news.topic', NULL, 'IS NOT NULL');
    $queryTopics->groupBy('news.topic');
    $actualTopics = $queryTopics->execute()->fetchCol();
    return $actualTopics;
  }
}