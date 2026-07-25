<?php

namespace Drupal\mangalpath_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Link;
use Drupal\Core\Url;



class NotificationController extends ControllerBase {

  /**
   * Unread notification count.
   */
  public function count() {

  $count = \Drupal::database()
    ->select('mangalpath_notifications', 'n')
    ->condition('is_read', 0)
    ->countQuery()
    ->execute()
    ->fetchField();

  return new JsonResponse([
    'count' => (int) $count,
  ]);

}

  /**
   * Latest notifications.
   */
  


public function list() {
  $query = Database::getConnection()
    ->select('mangalpath_notifications', 'n')
    ->fields('n')
    ->orderBy('created', 'DESC')
    ->range(0, 10);

  $rows = [];
  foreach ($query->execute() as $row) {
    // Create URL object
    $url = Url::fromUri('internal:' . $row->link);

    // Create Link render array
    $link = Link::fromTextAndUrl('View', $url)->toRenderable();

    $link['#attributes'] = [
  'class' => ['notification-view-link'],
  'data-id' => $row->id,
];

    $rows[] = [
  [
    'data' => $row->id,
  ],
  [
    'data' => $row->title,
  ],
  [
    'data' => $row->message,
  ],
  [
    'data' => $link, // Render array
  ],
  [
    'data' => date('d M Y h:i A', $row->created),
  ],
  [
    'data' => $row->is_read ? 'Read' : 'Unread',
  ],
];
  }

  $header = ['ID', 'Title', 'Message', 'Link', 'Created', 'Status'];

  return [
    '#type' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#attributes' => ['class' => ['notifications-table']],
  ];
}


public function markRead($id) {

  Database::getConnection()
    ->update('mangalpath_notifications')
    ->fields([
      'is_read' => 1,
    ])
    ->condition('id', $id)
    ->execute();

  return new JsonResponse([
    'status' => TRUE,
  ]);
}
}