<?php

namespace Drupal\dp_rss\Controller;

use Drupal\dp_rss\AggregateData;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RssController.
 */
class RssController extends ControllerBase {

  /**
   * @var AggregateData
   */
  private $data;

  public function __construct(AggregateData $data)
  {
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dp_rss.data')
    );
  }

  /**
   * RSS Zen generate.
   *
   * @return string
   *   Return XML.
   */
  public function rss_generate($destination) {
    $response = $this->data->getRssItems();
    return $response;
  }
}
