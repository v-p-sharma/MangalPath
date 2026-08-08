<?php

namespace Drupal\mangalpath_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

  // Validate required parameters.
  if (empty($nid) || empty($status)) {
    return new JsonResponse([
      'status' => FALSE,
      'message' => $this->t('Missing required parameters.'),
    ]);
  }

  $node = Node::load($nid);

  if (!$node) {
    return new JsonResponse([
      'status' => FALSE,
      'message' => $this->t('Node not found.'),
    ]);
  }

  // Compare as integers since status comes from request as a string.
  $node->set('field_partner_status', $status);
  if ((int) $status === 132) {
    $node->setPublished(TRUE);
  }
  if((int) $status === 134) {
    $node->setUnpublished();
  }
  else {
    // $node->setUnpublished();
  }

  $node->save();

  return new JsonResponse([
    'status' => TRUE,
  ]);

}
}