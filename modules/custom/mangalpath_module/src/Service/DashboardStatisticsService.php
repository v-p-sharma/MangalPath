<?php
declare(strict_types=1);
namespace Drupal\mangalpath_module\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;

/**
 * Provides partner dashboard statistics.
 */
class DashboardStatisticsService {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private AccountProxyInterface $currentUser;

  /**
   * Partner Status IDs.
   */
  private const STATUS_PENDING = 131;
  private const STATUS_LISTED = 132;
  private const STATUS_REJECTED = 135;
  private const STATUS_SOLD = 133;
  private const STATUS_INACTIVE = 134;

  /**
   * Property Purpose IDs.
   */
  private const PROPERTY_SELL = 72;
  private const PROPERTY_RENT = 73;

  /**
   * Vehicle Purpose IDs.
   */
  private const VEHICLE_SELL = 122;
  private const VEHICLE_RENT = 123;

  /**
   * Content type (bundle) machine names.
   */
  private const PROPERTY_BUNDLE = 'properties_listing_form';
  private const VEHICLE_BUNDLE = 'vehicle_sell';

  /**
   * Field machine names.
   */
  private const FIELD_STATUS = 'field_partner_status';
  private const FIELD_PROPERTY_PURPOSE = 'field_purpose_of';
  private const FIELD_PROPERTY_PRICE = 'field_expected_price';
  private const FIELD_VEHICLE_PURPOSE = 'field_vehicle_purpose';
  private const FIELD_VEHICLE_PRICE = 'field_expected_selling_price';

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user
  ) {

    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;

  }

  /**
   * Returns complete dashboard statistics.
   */
  public function getStatistics(): array {

    $roles = $this->currentUser->getRoles();

    $is_admin = in_array('administrator', $roles, TRUE);

    $uid = (int) $this->currentUser->id();

    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', [
        self::PROPERTY_BUNDLE,
        self::VEHICLE_BUNDLE,
      ], 'IN')
      ->condition('status', 1)
      ->accessCheck(FALSE);

    if (!$is_admin) {
      $query->condition('uid', $uid);
    }

    $nids = $query->execute();

    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nids);
          $statistics = [

  'common' => [

    'total_listing' => 0,
    'total_enquery' => 0,
    'total_pending' => 0,

    'total_listed' => 0,

    'total_rejected' => 0,

    'total_sold' => 0,

    'total_inactive' => 0,

    'total_revenue' => 0,

  ],

  'property' => [

    'total_listing' => 0,

    'total_sell' => 0,

    'total_rent' => 0,

    'total_pending' => 0,

    'total_listed' => 0,

    'total_rejected' => 0,

    'total_sold' => 0,

    'total_inactive' => 0,

    'revenue' => 0,

  ],

  'vehicle' => [

    'total_listing' => 0,

    'total_sell' => 0,

    'total_rent' => 0,
        'total_rent_vehicle'=> 0,
    'total_sell_vehicle' => 0,


    'total_pending' => 0,

    'total_listed' => 0,

    'total_rejected' => 0,

    'total_sold' => 0,

    'total_inactive' => 0,

    'revenue' => 0,

  ],

];
$property_nids = [];
$vehicle_sell_nids = [];
$vehicle_rent_nids = [];

    foreach ($nodes as $node) {

      if (!$node instanceof NodeInterface) {
        continue;
      }
          
      if ($node->bundle() === self::PROPERTY_BUNDLE) {
    $property_nids[] = $node->id();
  }
  elseif ($node->bundle() === self::VEHICLE_BUNDLE) {

    $purpose_tid = (int) $node
      ->get(self::FIELD_VEHICLE_PURPOSE)
      ->target_id;

      if ($purpose_tid === self::VEHICLE_SELL) {
      $vehicle_sell_nids[] = $node->id();
    }
    elseif ($purpose_tid === self::VEHICLE_RENT) {
      $vehicle_rent_nids[] = $node->id(); }



  }





      $statistics['common']['total_listing']++;

      $bundle = $node->bundle();

      $status_tid = $this->getPartnerStatusTid($node);

      switch ($status_tid) {

        case self::STATUS_PENDING:
          $statistics['common']['total_pending']++;
          break;

        case self::STATUS_LISTED:
          $statistics['common']['total_listed']++;
          break;

        case self::STATUS_REJECTED:
          $statistics['common']['total_rejected']++;
          break;

        case self::STATUS_SOLD:
          $statistics['common']['total_sold']++;
          break;

        case self::STATUS_INACTIVE:
          $statistics['common']['total_inactive']++;
          break;

      }

      if ($bundle === self::PROPERTY_BUNDLE) {

        $this->processPropertyStatistics(
          $node,
          $statistics,
          $status_tid
        );

      }
      elseif ($bundle === self::VEHICLE_BUNDLE) {

        $this->processVehicleStatistics(
          $node,
          $statistics,
          $status_tid
        );

      }

      
   $property_enquiry_count = 0;

if (!empty($property_nids)) {
  $property_enquiry_count = $this->entityTypeManager
    ->getStorage('node')
    ->getQuery()
    ->condition('type', 'property_enquiry_form')
    ->condition('field_property_id', $property_nids, 'IN')
    ->condition('status', 1)
    ->accessCheck(FALSE)
    ->count()
    ->execute();
}
$vehicle_enquiry_count = 0;

if (!empty($vehicle_sell_nids)) {
  $vehicle_enquiry_count = $this->entityTypeManager
    ->getStorage('node')
    ->getQuery()
    ->condition('type', 'vehicle_enquiry_form')
    ->condition('field_property_id', $vehicle_sell_nids, 'IN')
    ->condition('status', 1)
    ->accessCheck(FALSE)
    ->count()
    ->execute();
}
$tour_enquiry_count = 0;

if (!empty($vehicle_rent_nids)) {
  $tour_enquiry_count = $this->entityTypeManager
    ->getStorage('node')
    ->getQuery()
    ->condition('type', 'tour_enquiry_form')
    ->condition('field_property_id', $vehicle_rent_nids, 'IN')
    ->condition('status', 1)
    ->accessCheck(FALSE)
    ->count()
    ->execute();
}

$statistics['common']['total_enquery'] =
  $property_enquiry_count
  + $vehicle_enquiry_count
  + $tour_enquiry_count;


    }

    return $statistics;

  }
    /**
   * Process Property Statistics.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Property node.
   * @param array $statistics
   *   Statistics array.
   * @param int|null $status_tid
   *   Partner status term id.
   */
  private function processPropertyStatistics(
    NodeInterface $node,
    array &$statistics,
    ?int $status_tid
  ): void {

    $statistics['property']['total_listing']++;

    switch ($status_tid) {

      case self::STATUS_PENDING:
        $statistics['property']['total_pending']++;
        break;

      case self::STATUS_LISTED:
        $statistics['property']['total_listed']++;
        break;

      case self::STATUS_REJECTED:
        $statistics['property']['total_rejected']++;
        break;

      case self::STATUS_SOLD:
        $statistics['property']['total_sold']++;
        break;

      case self::STATUS_INACTIVE:
        $statistics['property']['total_inactive']++;
        break;

    }

    
     if($this->hasValue($node,self::FIELD_STATUS))
     {

      $purpose_tid = (int) $node
        ->get(self::FIELD_PROPERTY_PURPOSE)
        ->target_id;

      if ($purpose_tid === self::PROPERTY_SELL && $status_tid == self::STATUS_SOLD ) {

        $statistics['property']['total_sell']++;

      }
      elseif ($purpose_tid === self::PROPERTY_RENT &&  $status_tid == self::STATUS_SOLD) {

        $statistics['property']['total_rent']++;

      }

    }

    if ($status_tid === self::STATUS_SOLD) {

$price=$this->getFieldValue(
$node,
self::FIELD_PROPERTY_PRICE
);

      $amount = $this->normalizePrice($price);

      $statistics['property']['revenue'] += $amount;

      $statistics['common']['total_revenue'] += $amount;

    }

  }
    /**
   * Process Vehicle Statistics.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Vehicle node.
   * @param array $statistics
   *   Statistics array.
   * @param int|null $status_tid
   *   Partner status term id.
   */
  private function processVehicleStatistics(
    NodeInterface $node,
    array &$statistics,
    ?int $status_tid
  ): void {

    $statistics['vehicle']['total_listing']++;

    switch ($status_tid) {

      case self::STATUS_PENDING:
        $statistics['vehicle']['total_pending']++;
        break;

      case self::STATUS_LISTED:
        $statistics['vehicle']['total_listed']++;
        break;

      case self::STATUS_REJECTED:
        $statistics['vehicle']['total_rejected']++;
        break;

      case self::STATUS_SOLD:
        $statistics['vehicle']['total_sold']++;
        break;

      case self::STATUS_INACTIVE:
        $statistics['vehicle']['total_inactive']++;
        break;

    }

    
      if($this->hasValue($node,self::FIELD_STATUS))
     {

      $purpose_tid = (int) $node
        ->get(self::FIELD_VEHICLE_PURPOSE)
        ->target_id;
        

      if ($purpose_tid === self::VEHICLE_SELL && $status_tid == self::STATUS_SOLD) {

        $statistics['vehicle']['total_sell']++;

      }
      elseif ($purpose_tid === self::VEHICLE_RENT && $status_tid == self::STATUS_SOLD) {

        $statistics['vehicle']['total_rent']++;

      }
             if($purpose_tid === self::VEHICLE_SELL){
     $statistics['vehicle']['total_sell_vehicle']++;
     }else if($purpose_tid === self::VEHICLE_RENT) {
      $statistics['vehicle']['total_rent_vehicle']++;
     }


    }

    if ($status_tid === self::STATUS_SOLD) {

      $price=$this->getFieldValue(
$node,
self::FIELD_VEHICLE_PRICE
);

      $amount = $this->normalizePrice($price);

      $statistics['vehicle']['revenue'] += $amount;

      $statistics['common']['total_revenue'] += $amount;

    }

  }
    /**
   * Returns Partner Status Term ID.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Listing node.
   *
   * @return int|null
   *   Partner status term id.
   */
  private function getPartnerStatusTid(
    NodeInterface $node
  ): ?int {

    if (!$this->hasValue($node, self::FIELD_STATUS)) {

      return NULL;

    }

    return (int) $node
      ->get(self::FIELD_STATUS)
      ->target_id;

  }

  /**
   * Normalize any price format into numeric value.
   *
   * Supported Examples:
   * 1250000
   * 12,50,000
   * ₹12,50,000
   * Rs.1250000
   * INR 1250000
   * 12 lakh
   * 12 lac
   * 12.5 lakh
   * 1.5 cr
   * 1 crore
   *
   * @param string|null $price
   *   Raw price.
   *
   * @return float
   *   Numeric amount.
   */
  private function normalizePrice(?string $price): float {

    if (empty($price)) {
      return 0;
    }

    $price = strtolower(trim($price));

    $price = str_replace(
      [
        '₹',
        'rs.',
        'rs',
        'inr',
        ',',
      ],
      '',
      $price
    );

    $price = trim($price);

    if (preg_match('/([0-9]+(\.[0-9]+)?)\s*(crore|cr)/', $price, $matches)) {

      return (float) $matches[1] * 10000000;

    }

    if (preg_match('/([0-9]+(\.[0-9]+)?)\s*(lakh|lac|l)/', $price, $matches)) {

      return (float) $matches[1] * 100000;

    }

    if (is_numeric($price)) {

      return (float) $price;

    }

    $numeric = preg_replace('/[^0-9.]/', '', $price);

    return is_numeric($numeric)
      ? (float) $numeric
      : 0;

  }
  private function hasValue(
NodeInterface $node,
string $field
): bool {

  return
    $node->hasField($field)
    &&
    !$node->get($field)->isEmpty();

}
private function getFieldValue(
NodeInterface $node,
string $field
): string {

  if(!$this->hasValue($node,$field)){

    return '';

  }

  return trim(
    (string)$node->get($field)->value
  );

}

}