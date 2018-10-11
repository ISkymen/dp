<?php

namespace Drupal\si_stat;

/**
 * Value object for passing statistic results.
 */
class StatViewsResult {

  /**
   * @var int
   */
  protected $totalCount;

  /**
   * @var int
   */
  protected $yearCount;

  /**
   * @var int
   */
  protected $monthCount;

  /**
   * @var int
   */
  protected $weekCount;

  /**
   * @var int
   */
  protected $dayCount;

  /**
   * @var int
   */
  protected $timestamp;

  public function __construct($total_count, $year_count, $month_count, $week_count, $day_count, $timestamp) {
    $this->totalCount = $total_count;
    $this->yearCount = $year_count;
    $this->monthCount = $month_count;
    $this->weekCount = $week_count;
    $this->dayCount = $day_count;
    $this->timestamp = $timestamp;
  }

  /**
   * Total number of times the entity has been viewed.
   *
   * @return int
   */
  public function getTotalCount() {
    return $this->totalCount;
  }


  /**
   * Total number of times the entity has been viewed "This year".
   *
   * @return int
   */
  public function getYearCount() {
    return $this->yearCount;
  }

  /**
   * Total number of times the entity has been viewed "This month".
   *
   * @return int
   */
  public function getMonthCount() {
    return $this->monthCount;
  }

  /**
   * Total number of times the entity has been viewed "This week".
   *
   * @return int
   */
  public function getWeekCount() {
    return $this->weekCount;
  }

  /**
   * Total number of times the entity has been viewed "Today".
   *
   * @return int
   */
  public function getDayCount() {
    return $this->dayCount;
  }


  /**
   * Timestamp of when the entity was last viewed.
   *
   * @return int
   */
  public function getTimestamp() {
    return $this->timestamp;
  }
}
