<?php

namespace Drupal\mangalpath_payment\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

/**
 * Handles Razorpay payment operations.
 */
class PaymentService {

  /**
   * Razorpay API.
   *
   * @var \Razorpay\Api\Api
   */
  protected Api $api;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * Configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs PaymentService.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    Connection $database,
    AccountProxyInterface $current_user,
    LoggerChannelInterface $logger,
  ) {

    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->logger = $logger;

    $this->config = $config_factory->get('mangalpath_payment.settings');

    $key_id = $this->config->get('razorpay_key_id');
    $key_secret = $this->config->get('razorpay_key_secret');
\Drupal::logger('mangalpath_payment')->notice(
  'Key ID: @key',
  ['@key' => $key_id]
);
    $this->api = new Api(
      
      $key_id = "rzp_test_TH07mjja9Wmtit",
      $key_secret = "dSy674IvZkJzQ6DV15rasOhj"
    );

  }

    /**
   * Returns module configuration.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Payment configuration.
   */
  public function getKeyId(): string {

  if (!empty($GLOBALS['settings']['mangalpath_payment']['key_id'])) {
    return $GLOBALS['settings']['mangalpath_payment']['key_id'];
  }

  return (string) $this->config
    ->get('key_id');

}

public function getKeySecret(): string {

  if (!empty($GLOBALS['settings']['mangalpath_payment']['key_secret'])) {
    return $GLOBALS['settings']['mangalpath_payment']['key_secret'];
  }

  return (string) $this->config
    ->get('key_secret');

}

public function getWebhookSecret(): string {

  if (!empty($GLOBALS['settings']['mangalpath_payment']['webhook_secret'])) {
    return $GLOBALS['settings']['mangalpath_payment']['webhook_secret'];
  }

  return (string) $this->config
    ->get('webhook_secret');

}

public function getListingAmount(): float {

  return (float) $this->config
    ->get('listing_amount');

}

public function getCurrency(): string {

  return (string) $this->config
    ->get('currency');

}

public function isTestMode(): bool {

  return $this->config
    ->get('mode') === 'test';

}

  /**
   * Returns Razorpay API object.
   *
   * @return \Razorpay\Api\Api
   *   Razorpay API instance.
   */
  
  public function getApi(): Api {
    return $this->api;
  }

  /**
   * Returns current user ID.
   *
   * @return int
   *   Current user ID.
   */
  public function getCurrentUserId(): int {
    return (int) $this->currentUser->id();
  }
    /**
   * Creates Razorpay order.
   *
   * @param \Drupal\node\Entity\NodeInterface $node
   *   Listing node.
   * @param float|null $amount
   *   Listing amount.
   *
   * @return array
   *   Razorpay order response.
   *
   * @throws \Exception
   */
  public function createOrder(NodeInterface $node, ?float $amount = NULL): array {

    if (!$node->id()) {
      throw new \InvalidArgumentException('Invalid node supplied.');
    }

    if ($amount === NULL) {
      $amount = $this->getListingAmount();
    }

    if ($amount <= 0) {
      throw new \InvalidArgumentException('Invalid payment amount.');
    }

    try {

      // Razorpay accepts amount in paise.
      $razorpay_amount = (int) round($amount * 100);

      $receipt = sprintf(
        'NODE-%d-USER-%d-%d',
        $node->id(),
        $this->getCurrentUserId(),
        time()
      );

      $order = $this->api->order->create([
        'receipt' => $receipt,
        'amount' => $razorpay_amount,
        'currency' => $this->getCurrency(),
        'payment_capture' => true,
        'notes' => [
          'uid' => $this->getCurrentUserId(),
          'nid' => $node->id(),
          'bundle' => $node->bundle(),
          'title' => $node->label(),
        ],
      ]);

      $this->logger->info(
        'Razorpay order created. Order ID: @order | Node: @nid | User: @uid',
        [
          '@order' => $order['id'],
          '@nid' => $node->id(),
          '@uid' => $this->getCurrentUserId(),
        ]
      );

      return [
        'success' => TRUE,
        'order_id' => $order['id'],
        'amount' => $amount,
        'currency' => $this->getCurrency(),
        'razorpay_amount' => $razorpay_amount,
        'receipt' => $receipt,
        'node_id' => $node->id(),
        'user_id' => $this->getCurrentUserId(),
        'response' => $order->toArray(),
      ];

    }
    catch (\Exception $exception) {

      $this->logger->error(
        'Unable to create Razorpay order. Message: @message',
        [
          '@message' => $exception->getMessage(),
        ]
      );

      throw $exception;

    }

  }
    /**
   * Verifies Razorpay payment signature.
   *
   * @param string $order_id
   *   Razorpay Order ID.
   * @param string $payment_id
   *   Razorpay Payment ID.
   * @param string $signature
   *   Razorpay Signature.
   *
   * @return bool
   *   TRUE if signature is valid.
   *
   * @throws \Exception
   */
  public function verifySignature(
    string $order_id,
    string $payment_id,
    string $signature,
  ): bool {

    try {

      $attributes = [
        'razorpay_order_id' => $order_id,
        'razorpay_payment_id' => $payment_id,
        'razorpay_signature' => $signature,
      ];

      $this->api
        ->utility
        ->verifyPaymentSignature($attributes);

      $this->logger->notice(
        'Signature verified successfully. Order: @order Payment: @payment',
        [
          '@order' => $order_id,
          '@payment' => $payment_id,
        ]
      );

      return TRUE;

    }
    catch (SignatureVerificationError $exception) {

      $this->logger->error(
        'Signature verification failed. Order: @order Payment: @payment Message: @message',
        [
          '@order' => $order_id,
          '@payment' => $payment_id,
          '@message' => $exception->getMessage(),
        ]
      );

      return FALSE;

    }
    catch (\Exception $exception) {

      $this->logger->error(
        'Unexpected error during signature verification. Message: @message',
        [
          '@message' => $exception->getMessage(),
        ]
      );

      throw $exception;

    }

  }
    /**
   * Fetches payment details from Razorpay.
   *
   * @param string $payment_id
   *   Razorpay Payment ID.
   *
   * @return array
   *   Payment details.
   *
   * @throws \Exception
   */
  public function fetchPayment(string $payment_id): array {

    if (empty($payment_id)) {
      throw new \InvalidArgumentException('Payment ID is required.');
    }

    try {

      $payment = $this->api
        ->payment
        ->fetch($payment_id);

      $payment = $payment->toArray();

      $this->logger->notice(
        'Payment fetched successfully. Payment ID: @payment',
        [
          '@payment' => $payment_id,
        ]
      );

      return [
        'success' => TRUE,
        'payment_id' => $payment['id'] ?? '',
        'order_id' => $payment['order_id'] ?? '',
        'amount' => isset($payment['amount'])
          ? ($payment['amount'] / 100)
          : 0,
        'currency' => $payment['currency'] ?? 'INR',
        'status' => $payment['status'] ?? '',
        'method' => $payment['method'] ?? '',
        'bank' => $payment['bank'] ?? '',
        'wallet' => $payment['wallet'] ?? '',
        'vpa' => $payment['vpa'] ?? '',
        'email' => $payment['email'] ?? '',
        'contact' => $payment['contact'] ?? '',
        'fee' => isset($payment['fee'])
          ? ($payment['fee'] / 100)
          : 0,
        'tax' => isset($payment['tax'])
          ? ($payment['tax'] / 100)
          : 0,
        'captured' => !empty($payment['captured']),
        'international' => !empty($payment['international']),
        'refund_status' => $payment['refund_status'] ?? NULL,
        'raw' => $payment,
      ];

    }
    catch (\Exception $exception) {

      $this->logger->error(
        'Unable to fetch payment. Payment ID: @payment Message: @message',
        [
          '@payment' => $payment_id,
          '@message' => $exception->getMessage(),
        ]
      );

      throw $exception;

    }

  }
    /**
   * Captures an authorized Razorpay payment.
   *
   * Note:
   * If payment_capture = 1 while creating the order,
   * Razorpay captures the payment automatically.
   * This method is useful for manual capture mode.
   *
   * @param string $payment_id
   *   Razorpay Payment ID.
   * @param float|null $amount
   *   Capture amount.
   *
   * @return array
   *   Capture response.
   *
   * @throws \Exception
   */
  public function capturePayment(
    string $payment_id,
    ?float $amount = NULL,
  ): array {

    if (empty($payment_id)) {
      throw new \InvalidArgumentException('Payment ID is required.');
    }

    try {

      $payment = $this->api
        ->payment
        ->fetch($payment_id);

      // Already captured.
      if (!empty($payment['captured'])) {

        $this->logger->notice(
          'Payment already captured. Payment ID: @payment',
          [
            '@payment' => $payment_id,
          ]
        );

        return [
          'success' => TRUE,
          'already_captured' => TRUE,
          'response' => $payment->toArray(),
        ];

      }

      if ($amount === NULL) {
        $amount = $payment['amount'] / 100;
      }

      $capture_amount = (int) round($amount * 100);

      $capture = $payment->capture([
        'amount' => $capture_amount,
        'currency' => $payment['currency'],
      ]);

      $this->logger->notice(
        'Payment captured successfully. Payment ID: @payment',
        [
          '@payment' => $payment_id,
        ]
      );

      return [
        'success' => TRUE,
        'already_captured' => FALSE,
        'payment_id' => $payment_id,
        'amount' => $amount,
        'response' => $capture->toArray(),
      ];

    }
    catch (\Exception $exception) {

      $this->logger->error(
        'Unable to capture payment. Payment ID: @payment Message: @message',
        [
          '@payment' => $payment_id,
          '@message' => $exception->getMessage(),
        ]
      );

      throw $exception;

    }

  }
    /**
   * Saves payment transaction.
   *
   * @param array $data
   *   Transaction data.
   *
   * @return int
   *   Transaction ID.
   *
   * @throws \Exception
   */
  public function saveTransaction(array $data): int {

    if (empty($data['order_id'])) {
  throw new \InvalidArgumentException('Order ID is required.');
}

    try {

      // Check duplicate payment.
      $transaction_id = $this->database
        ->select('mangalpath_payment_transaction', 'pt')
        ->fields('pt', ['id'])
        ->condition('order_id', $data['order_id'])
        ->execute()
        ->fetchField();

      $fields = [
  'uid' => $data['uid'],
  'nid' => $data['nid'],
  'listing_type' => $data['listing_type'],
  'gateway' => 'razorpay',

  'order_id' => $data['order_id'] ?? '',

  'payment_id' => $data['payment_id'] ?? '',

  'signature' => $data['signature'] ?? '',

  'amount' => $data['amount'] ?? 0,

  'currency' => $data['currency'] ?? 'INR',

  'status' => $data['status'] ?? 'pending',

  'gateway_response' => json_encode(
    $data['gateway_response'] ?? [],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
  ),

  'changed' => time(),
];

      if ($transaction_id) {

        $this->database
          ->update('mangalpath_payment_transaction')
          ->fields($fields)
          ->condition('id', $transaction_id)
          ->execute();

        $this->logger->notice(
          'Payment transaction updated. ID: @id',
          [
            '@id' => $transaction_id,
          ]
        );

        return (int) $transaction_id;

      }

      $fields['created'] = time();

      $transaction_id = $this->database
        ->insert('mangalpath_payment_transaction')
        ->fields($fields)
        ->execute();

      $this->logger->notice(
        'Payment transaction created. ID: @id',
        [
          '@id' => $transaction_id,
        ]
      );

      return (int) $transaction_id;

    }
    catch (\Exception $exception) {

      $this->logger->error(
        'Unable to save payment transaction. Message: @message',
        [
          '@message' => $exception->getMessage(),
        ]
      );

      throw $exception;

    }

  }
    /**
   * Returns transaction by Order ID.
   *
   * @param string $order_id
   *   Razorpay Order ID.
   *
   * @return array|null
   *   Transaction row or NULL.
   */
  public function getTransactionByOrderId(string $order_id): ?array {

    $transaction = $this->database
      ->select('mangalpath_payment_transaction', 'pt')
      ->fields('pt')
      ->condition('order_id', $order_id)
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();

    return $transaction ?: NULL;

  }

  /**
   * Returns transaction by Payment ID.
   *
   * @param string $payment_id
   *   Razorpay Payment ID.
   *
   * @return array|null
   *   Transaction row or NULL.
   */
  public function getTransactionByPaymentId(string $payment_id): ?array {

    $transaction = $this->database
      ->select('mangalpath_payment_transaction', 'pt')
      ->fields('pt')
      ->condition('payment_id', $payment_id)
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();

    return $transaction ?: NULL;

  }

  /**
   * Returns latest transaction for a node.
   *
   * @param int $nid
   *   Node ID.
   *
   * @return array|null
   *   Transaction row or NULL.
   */
  public function getTransactionByNodeId(int $nid): ?array {

    $transaction = $this->database
      ->select('mangalpath_payment_transaction', 'pt')
      ->fields('pt')
      ->condition('nid', $nid)
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();

    return $transaction ?: NULL;

  }

  /**
   * Returns all transactions of a node.
   *
   * @param int $nid
   *   Node ID.
   *
   * @return array
   *   Transaction list.
   */
  public function getTransactionsByNodeId(int $nid): array {

    return $this->database
      ->select('mangalpath_payment_transaction', 'pt')
      ->fields('pt')
      ->condition('nid', $nid)
      ->orderBy('id', 'DESC')
      ->execute()
      ->fetchAllAssoc('id');

  }

  /**
   * Returns all transactions of a user.
   *
   * @param int $uid
   *   User ID.
   *
   * @return array
   *   Transaction list.
   */
  public function getTransactionsByUserId(int $uid): array {

    return $this->database
      ->select('mangalpath_payment_transaction', 'pt')
      ->fields('pt')
      ->condition('uid', $uid)
      ->orderBy('id', 'DESC')
      ->execute()
      ->fetchAllAssoc('id');

  }

  /**
   * Checks whether payment is completed.
   *
   * @param int $nid
   *   Node ID.
   *
   * @return bool
   *   TRUE if payment completed.
   */
  public function isPaymentCompleted(int $nid): bool {

    $status = $this->database
      ->select('mangalpath_payment_transaction', 'pt')
      ->fields('pt', ['status'])
      ->condition('nid', $nid)
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchField();

    return in_array($status, [
      'paid',
      'captured',
      'success',
    ], TRUE);

  }

  /**
   * Returns latest successful payment of a node.
   *
   * @param int $nid
   *   Node ID.
   *
   * @return array|null
   *   Transaction row.
   */
  public function getSuccessfulPayment(int $nid): ?array {

    $transaction = $this->database
      ->select('mangalpath_payment_transaction', 'pt')
      ->fields('pt')
      ->condition('nid', $nid)
      ->condition('status', [
        'paid',
        'captured',
        'success',
      ], 'IN')
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();

    return $transaction ?: NULL;

  }
    /**
   * Updates transaction status.
   *
   * @param int $transaction_id
   *   Transaction ID.
   * @param string $status
   *   New status.
   *
   * @return bool
   *   TRUE on success.
   */
  public function updateTransactionStatus(
    int $transaction_id,
    string $status,
  ): bool {

    try {

      $updated = $this->database
        ->update('mangalpath_payment_transaction')
        ->fields([
          'status' => $status,
          'changed' => time(),
        ])
        ->condition('id', $transaction_id)
        ->execute();

      if ($updated) {

        $this->logger->notice(
          'Transaction @id updated to @status.',
          [
            '@id' => $transaction_id,
            '@status' => $status,
          ]
        );

        return TRUE;

      }

      return FALSE;

    }
    catch (\Exception $exception) {

      $this->logger->error(
        'Unable to update transaction @id. @message',
        [
          '@id' => $transaction_id,
          '@message' => $exception->getMessage(),
        ]
      );

      throw $exception;

    }

  }

  /**
   * Creates pending transaction.
   *
   * Called immediately after Razorpay Order creation.
   *
   * @param \Drupal\node\Entity\NodeInterface $node
   *   Listing node.
   * @param array $order
   *   Order response.
   *
   * @return int
   *   Transaction ID.
   */
  public function createPendingTransaction(
    NodeInterface $node,
    array $order,
  ): int {

    return $this->saveTransaction([
      'uid' => $this->getCurrentUserId(),
      'nid' => $node->id(),
      'listing_type' => $node->bundle(),
      'order_id' => $order['order_id'],
      'payment_id' => '',
      'signature' => '',
      'amount' => $order['amount'],
      'currency' => $order['currency'],
      'status' => 'pending',
      'gateway_response' => $order['response'],
    ]);

  }

  /**
   * Marks payment failed.
   *
   * @param string $order_id
   *   Razorpay Order ID.
   *
   * @return bool
   *   TRUE on success.
   */
  public function markPaymentFailed(string $order_id): bool {

    $transaction = $this->getTransactionByOrderId($order_id);

    if (!$transaction) {
      return FALSE;
    }

    return $this->updateTransactionStatus(
      (int) $transaction['id'],
      'failed'
    );

  }

  /**
   * Marks payment successful.
   *
   * @param string $order_id
   *   Razorpay Order ID.
   *
   * @return bool
   *   TRUE on success.
   */
  public function markPaymentSuccess(string $order_id): bool {

    $transaction = $this->getTransactionByOrderId($order_id);

    if (!$transaction) {
      return FALSE;
    }

    return $this->updateTransactionStatus(
      (int) $transaction['id'],
      'success'
    );

  }

  /**
   * Deletes transaction.
   *
   * @param int $transaction_id
   *   Transaction ID.
   *
   * @return bool
   *   TRUE if deleted.
   */
  public function deleteTransaction(int $transaction_id): bool {

    $deleted = $this->database
      ->delete('mangalpath_payment_transaction')
      ->condition('id', $transaction_id)
      ->execute();

    if ($deleted) {

      $this->logger->notice(
        'Transaction deleted. ID: @id',
        [
          '@id' => $transaction_id,
        ]
      );

      return TRUE;

    }

    return FALSE;

  }

  /**
   * Returns payment statistics.
   *
   * @return array
   *   Statistics.
   */
  public function getStatistics(): array {

    $statistics = [];

    $statistics['total_transactions'] = (int) $this->database
      ->select('mangalpath_payment_transaction', 't')
      ->countQuery()
      ->execute()
      ->fetchField();

    $statistics['successful'] = (int) $this->database
      ->select('mangalpath_payment_transaction', 't')
      ->condition('status', [
        'success',
        'paid',
        'captured',
      ], 'IN')
      ->countQuery()
      ->execute()
      ->fetchField();

    $statistics['failed'] = (int) $this->database
      ->select('mangalpath_payment_transaction', 't')
      ->condition('status', 'failed')
      ->countQuery()
      ->execute()
      ->fetchField();

    $statistics['pending'] = (int) $this->database
      ->select('mangalpath_payment_transaction', 't')
      ->condition('status', 'pending')
      ->countQuery()
      ->execute()
      ->fetchField();

    return $statistics;

  }

  /**
 * Returns all transactions.
 */
public function getTransactions(
  array $filters = []
): array {

  $query = $this->database
    ->select(
      'mangalpath_payment_transaction',
      't'
    );

  $query->fields('t');

  if (!empty($filters['status'])) {

    $query->condition(
      'status',
      $filters['status']
    );

  }

  if (!empty($filters['uid'])) {

    $query->condition(
      'uid',
      $filters['uid']
    );

  }

  if (!empty($filters['order_id'])) {

    $query->condition(
      'order_id',
      '%' . $this->database->escapeLike($filters['order_id']) . '%',
      'LIKE'
    );

  }

  $query->orderBy(
    'created',
    'DESC'
  );

  $query->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
    ->limit(30);

$result = $query
  ->execute()
  ->fetchAll();

return $result;

}
/**
 * Dashboard Statistics.
 */
public function getDashboardStatistics(): array {
  $stats = $this->paymentService
  ->getDashboardStatistics();

  $table = 'mangalpath_payment_transaction';

  $statistics = [];

  $statistics['total_transactions'] = (int) $this->database
    ->select($table)
    ->countQuery()
    ->execute()
    ->fetchField();

  $statistics['success'] = (int) $this->database
    ->select($table)
    ->condition('status', 'success')
    ->countQuery()
    ->execute()
    ->fetchField();

  $statistics['failed'] = (int) $this->database
    ->select($table)
    ->condition('status', 'failed')
    ->countQuery()
    ->execute()
    ->fetchField();

  $statistics['pending'] = (int) $this->database
    ->select($table)
    ->condition('status', 'pending')
    ->countQuery()
    ->execute()
    ->fetchField();

  $statistics['revenue'] = (float) $this->database
    ->select($table)
    ->condition('status', 'success')
    ->addExpression('SUM(amount)', 'total')
    ->execute()
    ->fetchField();

  return ['stats' => [

  '#theme' => 'item_list',

  '#items' => [

    'Total Transactions : ' . $stats['total_transactions'],

    'Successful Payments : ' . $stats['success'],

    'Failed Payments : ' . $stats['failed'],

    'Pending Payments : ' . $stats['pending'],

    'Revenue : ₹' . number_format(
      $stats['revenue'],
      2
    ),

  ],

],
  ];

}
public function getPendingTransaction(int $nid): ?array {

  $transaction = $this->database
    ->select('mangalpath_payment_transaction', 't')
    ->fields('t')
    ->condition('nid', $nid)
    ->condition('status', 'pending')
    ->condition('created', \Drupal::time()->getRequestTime() - 1800, '>')
    ->orderBy('created', 'DESC')
    ->range(0, 1)
    ->execute()
    ->fetchAssoc();

  if (!$transaction) {
    return NULL;
  }

  return [
    'order_id' => $transaction['order_id'],
    'amount' => $transaction['amount'],
    'currency' => $transaction['currency'],
  ];

}
public function isOrderPaid(string $order_id): bool {

  return (bool) $this->database
    ->select('mangalpath_payment_transaction', 't')
    ->fields('t', ['id'])
    ->condition('order_id', $order_id)
    ->condition('status', 'success')
    ->range(0, 1)
    ->execute()
    ->fetchField();

}
/**
 * Verify Razorpay payment signature.
 */
public function verifyPayment(
  string $order_id,
  string $payment_id,
  string $signature
): bool {

  try {

    $attributes = [
      'razorpay_order_id' => $order_id,
      'razorpay_payment_id' => $payment_id,
      'razorpay_signature' => $signature,
    ];

    $this->api
      ->utility
      ->verifyPaymentSignature($attributes);

    $this->logger->notice(
      'Payment signature verified. Order: @order Payment: @payment',
      [
        '@order' => $order_id,
        '@payment' => $payment_id,
      ]
    );

    return TRUE;

  }
  catch (SignatureVerificationError $exception) {

    $this->logger->error(
      'Signature verification failed. Order: @order Payment: @payment Message: @message',
      [
        '@order' => $order_id,
        '@payment' => $payment_id,
        '@message' => $exception->getMessage(),
      ]
    );

    return FALSE;

  }
  catch (\Exception $exception) {

    $this->logger->error(
      'Unexpected verification error. @message',
      [
        '@message' => $exception->getMessage(),
      ]
    );

    return FALSE;

  }

}
}

