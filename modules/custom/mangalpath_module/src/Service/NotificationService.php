<?php

namespace Drupal\mangalpath_module\Service;

use Drupal\Core\Database\Connection;

class NotificationService {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Create notification.
   */
  public function create($title, $message, $type, $link = '', $entity_type = 'node', $entity_id = 0, $uid = 0) {

    $this->database->insert('mangalpath_notifications')
      ->fields([
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'uid' => $uid,
        'link' => $link,
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();

  }

}