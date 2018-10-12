<?php

/**
 * @file
 * Handles counts of node views via AJAX with minimal bootstrap.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

chdir('../../..');

$autoloader = require_once 'autoload.php';

$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();

$entity_types = $container
  ->get('config.factory')
  ->get('si_stat.settings')
  ->get('enabled_entity_types');

if ($entity_types) {
  $entity_id = filter_input(INPUT_POST, 'entity_id', FILTER_VALIDATE_INT);
  $entity_type = filter_input(INPUT_POST, 'entity_type', FILTER_DEFAULT);
  if ($entity_id && $entity_type) {
    if(isset($entity_types[$entity_type]) && $entity_types[$entity_type]) {
      $container->get('request_stack')->push(Request::createFromGlobals());
      $container->get('si_stat.storage.entity')->recordView($entity_type, $entity_id);
    }
  }
}
