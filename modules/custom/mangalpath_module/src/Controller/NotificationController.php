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
    // ->condition('is_read', false)
    ->orderBy('created', 'DESC')
    ->range(0, 10);

  $rows = [];
  foreach ($query->execute() as $row) {
    $link_path = trim($row->link);

try {
  // External URL
  if (filter_var($link_path, FILTER_VALIDATE_URL)) {
    $url = Url::fromUri($link_path);
  }
  else {
    // Internal path (/node/53, node/53, property/myproperty/45 etc.)
    $url = Url::fromUserInput('/' . ltrim($link_path, '/'));
  }

  $link = Link::fromTextAndUrl('View', $url)->toRenderable();
}
catch (\Exception $e) {
  // Invalid path hone par fallback
  $link = [
    '#markup' => '-',
  ];
}

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

   $build['wrapper'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => ['table-responsive container-fluid'],
            ],
        ];

    $build['wrapper']['table'] = [
    '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#attributes' => ['class' => ['table table-bordered table-striped notifications-table']],
        '#attached' => [
        'library' => [
          'mangalpath_module/notification',
        ],
      ],
        '#cache' => [
    'max-age' => 0,
  ],

    ];

  return $build;
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