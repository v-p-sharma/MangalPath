<?php
namespace Drupal\mangalpath_payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\mangalpath_payment\Service\PaymentService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentController extends ControllerBase
{

    /**
     * Payment Service.
     *
     * @var \Drupal\mangalpath_payment\Service\PaymentService
     */
    protected PaymentService $paymentService;

    /**
     * Entity Type Manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * Current User.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Constructor.
     */
    /**
     * Database.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected Connection $database;
    public function __construct(
        PaymentService $payment_service,
        EntityTypeManagerInterface $entity_type_manager,
        AccountProxyInterface $current_user,
        Connection $database,
    ) {

        $this->paymentService    = $payment_service;
        $this->entityTypeManager = $entity_type_manager;
        $this->currentUser       = $current_user;
        $this->database          = $database;

    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container): static
    {

        return new static(
            $container->get('mangalpath_payment.payment_service'),
            $container->get('entity_type.manager'),
            $container->get('current_user'),
            $container->get('database'),
        );
    }

    /**
     * Payment page.
     *
     * @param \Drupal\node\NodeInterface $node
     *   Listing node.
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *   Render array.
     */
    public function payment(NodeInterface $node)
    {

        // -----------------------------
        // Validate Bundle
        // -----------------------------
        $allowed_bundles = [
            'properties_listing_form',
            'vehicle_sell',
        ];

        if (! in_array($node->bundle(), $allowed_bundles, true)) {
            throw new NotFoundHttpException();
        }

        // -----------------------------
        // Node should exist
        // -----------------------------
        if (! $node->id()) {
            throw new NotFoundHttpException();
        }

        // -----------------------------
        // Only owner can pay
        // -----------------------------
        if ((int) $node->getOwnerId() !== (int) $this->currentUser->id()) {
            throw new AccessDeniedHttpException();
        }

        // -----------------------------
        // Already Paid?
        // -----------------------------
        if ($this->paymentService->isPaymentCompleted($node->id())) {

            $this->messenger()->addStatus(
                $this->t('Payment already completed.')
            );

            return new RedirectResponse($node->toUrl()->toString());

        }

        // -----------------------------
        // Listing Amount
        // -----------------------------
        $amount  = $this->paymentService->getListingAmount();
        $pending = $this->paymentService
            ->getPendingTransaction($node->id());

        if ($pending) {

            // Existing pending order use karo.
            $order = $pending;

        } else {

            // Naya Razorpay order create karo.
            $order = $this->paymentService->createOrder($node);

        }

        // -----------------------------
        // Create Razorpay Order
        // -----------------------------
        $order = $this->paymentService->createOrder(
            $node,
            $amount
        );

        // -----------------------------
        // Save Pending Transaction
        // -----------------------------
        $transaction_id = $this->paymentService
            ->createPendingTransaction(
                $node,
                $order
            );

        $pending = $this->paymentService
            ->getPendingTransaction($node->id());

        if ($pending) {

            // Old pending order use karo.
            $order = $pending;

        } else {

            // New Razorpay Order banao.
            $order = $this->paymentService
                ->createOrder($node);

        }

        // -----------------------------
        // Build Checkout Data
        // -----------------------------
        $build = [];

        $build = [
            '#theme'    => 'mangalpath_payment_checkout',
            '#payment'  => [
                'transaction_id' => $transaction_id,
                'node_id'        => $node->id(),
                'title'          => $node->label(),
                'amount'         => $amount,
                'currency'       => $order['currency'],
                'order_id'       => $order['order_id'],
                'key'            => $this->paymentService->getKeyId(),
                'uid'            => $this->currentUser->id(),
                'success_url'    => Url::fromRoute('mangalpath_payment.success')->toString(),
                'failed_url'     => Url::fromRoute('mangalpath_payment.failed')->toString(),
            ],
            '#attached' => [
                'library'        => [
                    'mangalpath_payment/checkout',
                ],
                'drupalSettings' => [
                    'mangalpathPayment' => [
                        'transactionId' => $transaction_id,
                        'nodeId'        => $node->id(),
                        'orderId'       => $order['order_id'],
                        'amount'        => $amount * 100,
                        'currency'      => $order['currency'],
                        'key'           => $this->paymentService->getKeyId(),
                        'successUrl'    => Url::fromRoute('mangalpath_payment.success')->toString(),
                        'failedUrl'     => Url::fromRoute('mangalpath_payment.failed')->toString(),
                    ],
                ],
            ],
            '#cache'    => [
                'max-age' => 0,
            ],
        ];

        return $build;

    }

/**
 * Payment success callback.
 */
/**
 * Payment success page.
 */
    public function success()
    {

        return [
            '#markup' => '
      <div class="payment-success-page">
        <h2>Payment Successful</h2>
        <p>Your listing has been published successfully.</p>
        <a href="/partner/dashboard" class="button button--primary">
          Go to Dashboard
        </a>
      </div>',
        ];

    }

/**
 * Payment failed callback.
 */
/**
 * Payment failed page.
 */
    public function failed()
    {

        return [
            '#markup' => '
      <div class="payment-failed-page">
        <h2>Payment Failed</h2>
        <p>Your payment was not completed.</p>
        <a href="/partner/dashboard" class="button">
          Back to Dashboard
        </a>
      </div>',
        ];

    }

/**
 * Razorpay Webhook.
 */
    public function webhook(Request $request): Response
    {

        try {
            $db_transaction = $this->database->startTransaction();
            $payload        = $request->getContent();

            $signature = $request->headers->get('X-Razorpay-Signature');

            if (empty($payload) || empty($signature)) {
                return new Response('Invalid Request', 400);
            }

            $secret = $this->paymentService->getWebhookSecret();

            $generated_signature = hash_hmac(
                'sha256',
                $payload,
                $secret
            );

            if (! hash_equals($generated_signature, $signature)) {
                return new Response('Invalid Signature', 400);
            }

            $data = json_decode($payload, true);

            if (empty($data['event'])) {
                return new Response('Invalid Payload', 400);
            }

            switch ($data['event']) {

                case 'payment.captured':

                    $payment = $data['payload']['payment']['entity'];
                    if ($this->paymentService->isOrderPaid($payment['order_id'])) {
                        return new Response('Already Processed', 200);
                    }

                    $transaction = $this->paymentService
                        ->getTransactionByOrderId($payment['order_id']);

                    if ($transaction) {

                        $this->paymentService
                            ->markPaymentSuccess($payment['order_id']);

                        $node = $this->entityTypeManager
                            ->getStorage('node')
                            ->load($transaction['nid']);

                        if ($node instanceof NodeInterface) {

                            if (! $node->isPublished()) {
                                $node->setPublished(true);
                            }

                            $node->set(
                                'field_suscribed_amount',
                                $payment['amount'] / 100
                            );

                            $node->set(
                                'field_is_suscribed',
                                true
                            );

                            $node->save();

                        }

                    }

                    break;

                case 'payment.failed':

                    $payment = $data['payload']['payment']['entity'];

                    $this->paymentService
                        ->markPaymentFailed($payment['order_id']);

                    break;

            }

            return new Response('OK', 200);

        } catch (\Exception $exception) {

            $db_transaction->rollBack();

            \Drupal::logger('mangalpath_payment')
                ->error($exception->getMessage());

            return new Response('ERROR', 500);

        }

    }

/**
 * Returns webhook secret.
 */
    public function getWebhookSecret(): string
    {

        return (string) $this->config
            ->get('webhook_secret');

    }

    /**
     * Retry payment.
     */
    public function retry(NodeInterface $node)
    {

        if (! $node) {
            throw new NotFoundHttpException();
        }

        if (
            ! $this->currentUser->hasPermission('administer mangalpath payment') &&
            $node->getOwnerId() != $this->currentUser->id()
        ) {
            throw new AccessDeniedHttpException();
        }

        // Retry only unpublished listings.
        if ($node->isPublished()) {

            $this->messenger()->addStatus(
                $this->t('This listing is already published.')
            );

            return $this->redirect(
                'entity.node.canonical',
                ['node' => $node->id()]
            );

        }

        return $this->redirect(
            'mangalpath_payment.payment',
            [
                'node' => $node->id(),
            ]
        );

    }

/**
 * Payment History.
 */
    public function history()
    {

        $header = [

            'order_id'   => $this->t('Order ID'),

            'listing'    => $this->t('Listing'),

            'amount'     => $this->t('Amount'),

            'status'     => $this->t('Status'),

            'payment_id' => $this->t('Payment ID'),

            'created'    => $this->t('Created'),

            'operations' => $this->t('Operations'),

        ];

        $rows = [];
        $uid  = (int) $this->currentUser->id();

        if ($uid === 0) {

            throw new AccessDeniedHttpException();

        }
        $transactions = $this->paymentService
            ->getUserTransactions(
                $uid
            );
        if (
            ! $this->currentUser->hasPermission(
                'view payment reports'
            )
        ) {

            throw new AccessDeniedHttpException();

        }

        foreach ($transactions as $transaction) {
            $listing = '-';

            $node = $this->entityTypeManager
                ->getStorage('node')
                ->load($transaction->nid);

            if ($node instanceof NodeInterface) {
                $listing = $node->label();
            }
            $status = match ($transaction->payment_status) {

                'success' => '<span class="badge bg-success">Success</span>',

                'pending' => '<span class="badge bg-warning">Pending</span>',

                'failed'  => '<span class="badge bg-danger">Failed</span>',

                default   => '<span class="badge bg-secondary">Unknown</span>',

            };
            $operations = [];

            if ($transaction->payment_status == 'failed') {

                $operations[] = Link::fromTextAndUrl(
                    'Retry',
                    Url::fromRoute(
                        'mangalpath_payment.retry',
                        [
                            'node' => $transaction->nid,
                        ]
                    )
                )->toString();

            }

            $operations[] = Link::fromTextAndUrl(
                'View',
                Url::fromRoute(
                    'entity.node.canonical',
                    [
                        'node' => $transaction->nid,
                    ]
                )
            )->toString();

            $rows[] = [

                'order_id'   => $transaction->order_id,

                'listing'    => $listing,

                'amount'     => '₹' . number_format(
                    $transaction->amount,
                    2
                ),
                'status'     => [
                    'data' => [
                        '#markup' => $status,
                    ],
                ],

                'payment_id' => $transaction->payment_id,

                'created'    => \Drupal::service('date.formatter')
                    ->format(
                        $transaction->created,
                        'short'
                    ),

                'operations' => [
                    'data' => [
                        '#markup' => implode(' | ', $operations),
                    ],
                ],

            ];

        }

        return [

            'table' => [

                '#type'   => 'table',

                '#header' => $header,

                '#rows'   => $rows,

                '#empty'  => $this->t(
                    'No payments found.'
                ),

            ],
            'pager' => [
                '#type' => 'pager',
            ],

        ];

    }
/**
 * Returns user transactions.
 */
    public function getUserTransactions(int $uid): array
    {

        $query = $this->database
            ->select('mangalpath_payment_transaction', 't');

        $query->fields('t');

        $query->condition('uid', $uid);

        $query->orderBy('created', 'DESC');

        $query->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
            ->limit(20);

        return $query
            ->execute()
            ->fetchAll();

    }
    /**
     * Admin Transactions.
     */
    public function adminTransactions()
    {

        $transactions = $this->paymentService
            ->getTransactions();

        $header = [

            'order'   => 'Order ID',

            'user'    => 'User',

            'listing' => 'Listing',

            'amount'  => 'Amount',

            'status'  => 'Status',

            'payment' => 'Payment ID',

            'created' => 'Created',

        ];

        $rows     = [];
        $user_ids = [];
        $node_ids = [];

        foreach ($transactions as $transaction) {

            if (! empty($transaction->uid)) {
                $user_ids[] = $transaction->uid;
            }

            if (! empty($transaction->nid)) {
                $node_ids[] = $transaction->nid;
            }

        }

        $user_ids = array_unique($user_ids);
        $node_ids = array_unique($node_ids);

        $users = $this->entityTypeManager
            ->getStorage('user')
            ->loadMultiple($user_ids);

        $nodes = $this->entityTypeManager
            ->getStorage('node')
            ->loadMultiple($node_ids);

        foreach ($transactions as $transaction) {

            $username = '-';

            if (isset($users[$transaction->uid])) {
                $username = $users[$transaction->uid]
                    ->getDisplayName();
            }

            $listing = '-';

            if (isset($nodes[$transaction->nid])) {

                $listing = $nodes[$transaction->nid]
                    ->label();

            }

            $rows[] = [

                'order'   => $transaction->order_id,

                'user'    => $username,

                'listing' => $listing,

                'amount'  => '₹' . number_format(
                    $transaction->amount,
                    2
                ),

                'status'  => ucfirst(
                    $transaction->payment_status
                ),

                'payment' => $transaction->payment_id,

                'created' => \Drupal::service('date.formatter')
                    ->format(
                        $transaction->created,
                        'short'
                    ),

            ];

        }

        return [

            'table' => [

                '#type'   => 'table',

                '#header' => $header,

                '#rows'   => $rows,

                '#empty'  => 'No transactions found.',

            ],

            'pager' => [

                '#type' => 'pager',

            ],

        ];

    }
    /**
     * Razorpay payment success callback.
     */
    public function complete(Request $request): JsonResponse
    {

        try {

            $order_id   = $request->request->get('order_id');
            $payment_id = $request->request->get('payment_id');
            $signature  = $request->request->get('signature');

            if (
                empty($order_id) ||
                empty($payment_id) ||
                empty($signature)
            ) {

                return new JsonResponse([
                    'status'  => false,
                    'message' => 'Invalid payment request.',
                ], 400);

            }

            // Prevent duplicate processing.
            if ($this->paymentService->isOrderPaid($order_id)) {

                return new JsonResponse([
                    'status'  => true,
                    'message' => 'Payment already processed.',
                ]);

            }

            // Verify Razorpay signature.
            $verified = $this->paymentService->verifyPayment(
                $order_id,
                $payment_id,
                $signature
            );

            if (! $verified) {

                return new JsonResponse([
                    'status'  => false,
                    'message' => 'Payment verification failed.',
                ], 400);

            }

            // Fetch payment details from Razorpay.
            $payment = $this->paymentService
                ->fetchPayment($payment_id);

            // Get pending transaction.
            $transaction = $this->paymentService
                ->getTransactionByOrderId($order_id);

            if (! $transaction) {

                return new JsonResponse([
                    'status'  => false,
                    'message' => 'Transaction not found.',
                ], 404);

            }

            // Update transaction.
            $this->paymentService
                ->markPaymentSuccess($order_id);

            // Publish node.
            $node = Node::load($transaction['nid']);

            if ($node) {

                $node->setPublished(true);
                $node->save();

            }

            return new JsonResponse([
                'status'   => true,
                'redirect' => Url::fromRoute(
                    'mangalpath_payment.success'
                )->toString(),
            ]);

        } catch (\Exception $e) {

            \Drupal::logger('mangalpath_payment')
                ->error($e->getMessage());

            return new JsonResponse([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);

        }

    }

}
