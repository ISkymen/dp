<?php

namespace Drupal\dp_rss;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\HttpFoundation\Response;


class AggregateData implements ContainerInjectionInterface {

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

  public function getItems() {

    $queryNews = $this->database->select('news_field_data', 'news');
    $queryNews->fields('news', array(
      'id', 'title', 'body__value', 'image__target_id', 'rubric', 'created'
    ));
    $queryNews->condition('news.rss', 1);
    $queryNews->condition('news.status', 1);
    $queryNews->orderBy('news.created', 'desc');
    $queryNews->range(0, 20);

   $items = $queryNews->execute()->fetchAll(\PDO::FETCH_ASSOC);

    return $items;
  }

  public function getRssItems() {
    $items = $this->getItems();

    $response = new Response();
    $response->headers->set('Content-Type', 'application/xml');
    $response->setContent('');

    $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?>' . '<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss" version="2.0"></rss>');

    $mainChannel = $xml->addChild('channel');
    $mainChannel->addChild('title', 'Независимое интернет-издание «ДонПресс»');
    $mainChannel->addChild('link', 'https://donpress.com/');
    $mainChannel->addChild('description', 'Независимое интернет-издание «ДонПресс»');
    $mainChannel->addChild('language', 'ru');

    foreach ($items as $key => $item) {
      if (isset($item['image__target_id']) && !empty($item['image__target_id'])) {
        $image = File::load($item['image__target_id']);
        $image_url = ImageStyle::load('xlwd')->buildUrl($image->getFileUri());
        $image_type = $image->getMimeType();
      }
      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/news/' . $item['id']);
      $category = $this->zenCategory($item['rubric']);

      $itemChannel = $mainChannel->addChild('item');

      $itemChannel->addChild('title', $item['title']);
      $itemChannel->addChild('link', 'https://donpress.com' . $alias);
      $itemChannel->addChild('pubDate', date(DATE_RSS, $item['created']));
      $itemChannel->addChild('author', 'ДонПресс');
      if (isset($category)) {
        $itemChannel->addChild('category', $category);
      }
      $itemChannel->addChild('enclosure', 'url="' . $image_url . '" type="' . $image_type . '"');
      $itemChannel->addChildWithCDATA('description', text_summary(strip_tags($item['body__value']), 'basic_html'));
      $itemChannel->addChildWithCDATA('xmlns:content:encoded', $item['body__value']);
    }

    $out_xml = $xml->asXML();
    $response->setContent($out_xml);

    return $response;
  }

  private function zenCategory ($rubric_id) {
    switch ($rubric_id) {
      case 944:
        return "Авто";
      case 1171:
        return "Война";
      case 1440:
        return "Здоровье";
      case 15566:
        return "Знаменитости";
      case 2:
        return "Культура";
      case 16129:
        return "Мода";
      case 1500:
        return "Наука";
      case 365:
        return "Общество";
      case 1170:
        return "Политика";
      case 868:
        return "Происшествия";
      case 1076:
        return "Спорт";
      case 1122:
        return "Технологии";
      case 836:
        return "Экономика";
      default:
        return NULL;
    }
  }
}