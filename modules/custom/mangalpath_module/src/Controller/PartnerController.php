<?php

namespace Drupal\mangalpath_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Property Listing Controller.
 */
class PartnerController extends ControllerBase {

  /**
   * Property Listing Table.
   */

public function updateStatus(Request $request) {

  $nid = $request->request->get('nid');
  $status = $request->request->get('status');

  $node = Node::load($nid);

  if (!$node) {
    return new JsonResponse([
      'status' => FALSE,
    ]);
  }

  $node->set('field_partner_data_status', $status);
   if ($status === 132) {
    $node->setPublished(TRUE);
  }
  else {
    $node->setUnpublished();
  }

  $node->save();

  return new JsonResponse([
    'status' => TRUE,
  ]);

}
}