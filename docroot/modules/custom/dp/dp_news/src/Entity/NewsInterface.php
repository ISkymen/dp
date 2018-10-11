<?php

namespace Drupal\dp_news\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining News entities.
 *
 * @ingroup dp_news
 */
interface NewsInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the News name.
   *
   * @return string
   *   Name of the News.
   */
  public function getTitle();

  /**
   * Sets the News name.
   *
   * @param string $name
   *   The News name.
   *
   * @return \Drupal\dp_news\Entity\NewsInterface
   *   The called News entity.
   */
  public function setTitle($title);

  /**
   * Gets the News creation timestamp.
   *
   * @return int
   *   Creation timestamp of the News.
   */
  public function getCreatedTime();

  /**
   * Sets the News creation timestamp.
   *
   * @param int $timestamp
   *   The News creation timestamp.
   *
   * @return \Drupal\dp_news\Entity\NewsInterface
   *   The called News entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the News published status indicator.
   *
   * Unpublished News are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the News is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a News.
   *
   * @param bool $published
   *   TRUE to set this News to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\dp_news\Entity\NewsInterface
   *   The called News entity.
   */
  public function setPublished($published);

}
