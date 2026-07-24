<?php

namespace Drupal\mangalpath_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Vehicle Listing Controller.
 */
class VehicleListingController extends ControllerBase {

  /**
   * Vehicle Listing Table.
   */
  public function listing() {

    $account = \Drupal::currentUser();
    $roles = $account->getRoles();

    $storage = \Drupal::entityTypeManager()->getStorage('node');

    //==========================
    // Count Query
    //==========================
    $count_query = $storage->getQuery()
      ->condition('type', 'vehicle_sell')
      ->condition('status', 1)
      ->condition('field_vehicle_purpose.target_id', 122)
      ->accessCheck(TRUE);

    // Partner => Own Vehicles Only.
    if (in_array('partner', $roles, TRUE)) {
      $count_query->condition('uid', $account->id());
    }

    $total = $count_query->count()->execute();

    $limit = 10;

    $pager = \Drupal::service('pager.manager')->createPager($total, $limit);

    $current_page = $pager->getCurrentPage();

    //==========================
    // Listing Query
    //==========================
    $query = $storage->getQuery()
      ->condition('type', 'vehicle_sell')
      ->condition('status', 1)
      ->condition('field_vehicle_purpose.target_id', 122)
      ->sort('created', 'DESC')
      ->range($current_page * $limit, $limit)
      ->accessCheck(TRUE);

    // Partner => Own Vehicles Only.
    if (in_array('partner', $roles, TRUE)) {
      $query->condition('uid', $account->id());
    }

    $nids = $query->execute();

    $rows = [];

    if (!empty($nids)) {

      $nodes = $storage->loadMultiple($nids);

      foreach ($nodes as $node) {

        // Vehicle Purpose.
        $purpose = '';

        if (
          $node->hasField('field_vehicle_purpose') &&
          !$node->get('field_vehicle_purpose')->isEmpty()
        ) {
          $purpose = $node->get('field_vehicle_purpose')->entity->label();
        }

        // Vehicle Brand.
        $brand = '';

        if (
          $node->hasField('field_vehicle_brand') &&
          !$node->get('field_vehicle_brand')->isEmpty()
        ) {
          $brand = $node->get('field_vehicle_brand')->value;
        }

        // Partner Status.
        $partner_status = '';

        if (
          $node->hasField('field_partner_status') &&
          !$node->get('field_partner_status')->isEmpty()
        ) {
          $partner_status = $node->get('field_partner_status')->entity->label();
          $partner_statusId = $node->get('field_partner_status')[0]->target_id;
        }
        

        $isSuscription = false;
        if (
          $node->hasField('field_is_suscribed')
          && !$node->get('field_is_suscribed')->isEmpty()
        ) {
          $partner_subscribedStatus = $node->get('field_is_suscribed')[0]->value;
        }
        $SuscriptionAmount = "";
        if (
          $node->hasField('field_suscribed_amount')
          && !$node->get('field_suscribed_amount')->isEmpty()
        ) {
          $partner_subscribedAmount = $node->get('field_suscribed_amount')[0]->value;
        }
        $subscriptions = "";
        if($partner_subscribedStatus && $partner_subscribedAmount){
            $subscriptions = $partner_subscribedAmount;
        }
        $id = $node->id();
        // SEO URL.
        $alias = '/vehicle/' .
          $this->slugify($node->getTitle()) .
          '/' .
          $node->id();

        $rows[] = [
          'title' => $node->getTitle(),
          'id' => $id,
          'partner_statusId' =>$partner_statusId,
          'purpose' => $purpose,
          'brand' => $brand,
          'subscriptions' => $subscriptions,
          'created' => date('d M Y', $node->getCreatedTime()),
          'partner_status' => $partner_status,
          'action' => Link::fromTextAndUrl(
            'View',
            Url::fromUri('internal:' . $alias)
          )->toString(),
        ];

      }

    }
        return [
      '#theme' => 'partner_vehicle_listing',
      '#rows' => $rows,
      '#pager' => [
        '#type' => 'pager',
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];

  }

  /**
   * Convert title to URL slug.
   */
  private function slugify($string) {

    $string = strtolower(trim($string));

    $string = preg_replace('/[^a-z0-9]+/', '-', $string);

    return trim($string, '-');

  }

}