<?php

namespace Drupal\mangalpath_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Property Enquiry Listing Controller.
 */
class PropertyEnquiryListingController extends ControllerBase {

  /**
   * Property Enquiry Listing.
   */
  public function listing() {

    $account = \Drupal::currentUser();
    $roles = $account->getRoles();

    $storage = \Drupal::entityTypeManager()->getStorage('node');

    //==========================
    // Count Query
    //==========================
    $count_query = $storage->getQuery()
      ->condition('type', 'property_enquiry_form')
      ->condition('status', 1)
      ->accessCheck(TRUE);

    // Partner => Only own enquiries.
    if (in_array('partner', $roles, TRUE)) {
      $count_query->condition('field_property_owner_id', $account->id());
    }

    $total = $count_query->count()->execute();

    $limit = 10;

    $pager = \Drupal::service('pager.manager')->createPager($total, $limit);

    $current_page = $pager->getCurrentPage();

    //==========================
    // Listing Query
    //==========================
    $query = $storage->getQuery()
      ->condition('type', 'property_enquiry_form')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range($current_page * $limit, $limit)
      ->accessCheck(TRUE);

    if (in_array('partner', $roles, TRUE)) {
      $query->condition('field_property_owner_id', $account->id());
    }

    $nids = $query->execute();

    $rows = [];

    if (!empty($nids)) {

      $nodes = $storage->loadMultiple($nids);

      foreach ($nodes as $node) {

        // Customer Name.
        $name = '';
        if ($node->hasField('field_name') && !$node->get('field_name')->isEmpty()) {
          $name = $node->get('field_name')->value;
        }

        // Email.
        $email = '';
        if ($node->hasField('field_email') && !$node->get('field_email')->isEmpty()) {
          $email = $node->get('field_email')->value;
        }

        // Phone.
        $phone = '';
        if ($node->hasField('field_phone_number_data') && !$node->get('field_phone_number_data')->isEmpty()) {
          $phone = $node->get('field_phone_number_data')->value;
        }

        // Property ID.
        $property_id = '';
        $property_title = '';

        if ($node->hasField('field_property_id') && !$node->get('field_property_id')->isEmpty()) {

          $property_id = $node->get('field_property_id')->value;

          if ($property = Node::load($property_id)) {
            $property_title = $property->getTitle();
          }

        }

        // Owner.
        $owner_name = '';

        if ($node->hasField('field_property_owner_id') && !$node->get('field_property_owner_id')->isEmpty()) {

          $owner_id = $node->get('field_property_owner_id')->value;

          if ($user = User::load($owner_id)) {
            $owner_name = $user->getDisplayName();
          }

        }

        // View URL.
        $view = '';

        if (!empty($property_id) && isset($property)) {

          $alias = '/properties/' .
            $this->slugify($property->getTitle()) .
            '/' .
            $property->id();

          $view = Link::fromTextAndUrl(
            'View',
            Url::fromUri('internal:' . $alias)
          )->toString();

        }

        $rows[] = [
          'name' => $name,
          'email' => $email,
          'phone' => $phone,
          'property' => $property_title,
          'owner' => $owner_name,
          'created' => date('d M Y', $node->getCreatedTime()),
          'action' => $view,
        ];

      }

    }
        $is_admin = in_array('administrator', $roles, TRUE) || in_array('admin', $roles, TRUE);

    return [
      '#theme' => 'partner_property_enquiry_listing',
      '#rows' => $rows,
      '#is_admin' => $is_admin,
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
