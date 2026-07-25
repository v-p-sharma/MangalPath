<?php
namespace Drupal\partner_dashboard\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Common Enquiry Form.
 */
class CommonEnquiryForm extends FormBase
{

    /**
     * Entity Type Manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * Route Match.
     *
     * @var \Drupal\Core\Routing\RouteMatchInterface
     */
    protected $routeMatch;

    /**
     * Current User.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Messenger.
     *
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     */
    public function __construct(
        EntityTypeManagerInterface $entity_type_manager,
        RouteMatchInterface $route_match,
        AccountProxyInterface $current_user,
        MessengerInterface $messenger,
        LoggerChannelFactoryInterface $logger_factory
    ) {

        $this->entityTypeManager = $entity_type_manager;
        $this->routeMatch        = $route_match;
        $this->currentUser       = $current_user;
        $this->messenger         = $messenger;
        $this->logger            = $logger_factory->get('partner_dashboard');

    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {

        return new static(

            $container->get('entity_type.manager'),

            $container->get('current_route_match'),

            $container->get('current_user'),

            $container->get('messenger'),

            $container->get('logger.factory')

        );

    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {

        return 'common_enquiry_form';

    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $listing = $this->routeMatch->getParameter('node');

        if (! $listing instanceof Node) {

            $form['message'] = [
                '#markup' => '<div class="alert alert-danger">Listing not found.</div>',
            ];

            return $form;

        }

        $allowed_bundles = [
            'properties_listing_form',
            'vehicle_sell',
        ];

        if (! in_array($listing->bundle(), $allowed_bundles, true)) {

            $form['message'] = [
                '#markup' => '<div class="alert alert-warning">Invalid Listing.</div>',
            ];

            return $form;

        }

        $form['wrapper'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => [
                    'row',
                    'g-3',
                ],
            ],
        ];

        $form['wrapper']['field_name'] = [
            '#type'       => 'textfield',
            '#title'      => $this->t('Full Name'),
            '#required'   => true,
            '#maxlength'  => 100,
            '#attributes' => [
                'class'       => [
                    'form-control',
                ],
                'placeholder' => $this->t('Enter your full name'),
            ],
            '#prefix'     => '<div class="col-md-6">',
            '#suffix'     => '</div>',
        ];

        $form['wrapper']['field_phone'] = [
            '#type'       => 'textfield',
            '#title'      => $this->t('Mobile Number'),
            '#required'   => true,
            '#maxlength'  => 10,
            '#attributes' => [
                'class'       => [
                    'form-control',
                ],
                'placeholder' => $this->t('Enter Mobile Number'),
            ],
            '#prefix'     => '<div class="col-md-6">',
            '#suffix'     => '</div>',
        ];

        $form['wrapper']['field_email'] = [
            '#type'       => 'email',
            '#title'      => $this->t('Email Address'),
            '#required'   => false,
            '#attributes' => [
                'class'       => [
                    'form-control',
                ],
                'placeholder' => $this->t('Enter Email Address'),
            ],
            '#prefix'     => '<div class="col-md-6">',
            '#suffix'     => '</div>',
        ];
        $form['wrapper']['body'] = [
            '#type'       => 'textarea',
            '#title'      => $this->t('Message'),
            '#rows'       => 5,
            '#attributes' => [
                'class'       => [
                    'form-control',
                ],
                'placeholder' => $this->t('Tell us about your requirement...'),
            ],
            '#prefix'     => '<div class="col-md-12">',
            '#suffix'     => '</div>',
        ];

        $form['wrapper']['submit'] = [
            '#type'       => 'submit',
            '#value'      => $this->t('Request Callback'),
            '#attributes' => [
                'class' => [
                    'btn-submit',
                ],
            ],
            '#prefix'     => '<div class="col-md-12">',
            '#suffix'     => '</div>',
        ];

        return $form;

    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {

        $name    = trim($form_state->getValue('field_name'));
        $phone   = trim($form_state->getValue('field_phone'));
        $email   = trim($form_state->getValue('field_email'));
        $message = trim($form_state->getValue('body'));

        /*
    |--------------------------------------------------------------------------
    | Name Validation
    |--------------------------------------------------------------------------
    */

        if ($name === '') {

            $form_state->setErrorByName(
                'field_name',
                $this->t('Full Name is required.')
            );

        } elseif (mb_strlen($name) < 3) {

            $form_state->setErrorByName(
                'field_name',
                $this->t('Full Name must be at least 3 characters.')
            );

        } elseif (! preg_match('/^[a-zA-Z ]+$/', $name)) {

            $form_state->setErrorByName(
                'field_name',
                $this->t('Please enter a valid full name.')
            );

        }

        /*
    |--------------------------------------------------------------------------
    | Mobile Validation
    |--------------------------------------------------------------------------
    */

        if ($phone === '') {

            $form_state->setErrorByName(
                'field_phone',
                $this->t('Mobile Number is required.')
            );

        } elseif (! preg_match('/^[6-9][0-9]{9}$/', $phone)) {

            $form_state->setErrorByName(
                'field_phone',
                $this->t('Please enter a valid 10 digit mobile number.')
            );

        }

        /*
    |--------------------------------------------------------------------------
    | Email Validation
    |--------------------------------------------------------------------------
    */

        if (! empty($email) && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $form_state->setErrorByName(
                'field_email',
                $this->t('Please enter a valid email address.')
            );

        }

        /*
    |--------------------------------------------------------------------------
    | Message Validation
    |--------------------------------------------------------------------------
    */

        if (! empty($message) && mb_strlen($message) > 1000) {

            $form_state->setErrorByName(
                'body',
                $this->t('Message should not exceed 1000 characters.')
            );

        }

    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        try {

            $listing = $this->routeMatch->getParameter('node');
          

            if (! $listing instanceof Node) {

                throw new \Exception('Listing not found.');

            }

            $listing_id  = $listing->id();
            $partner_id  = $listing->getOwnerId();
            $customer_id = $this->currentUser->id();
            $bundle      = $listing->bundle();

            if($bundle == "properties_listing_form") {
             $related_inquery = "Properties Related inquries";
            }
            if($bundle == "vehicle_sell"){
              $related_inquery = "Vehicle Related Inquiries";
            }
            // .......................................Send Mail............................

            $mailManager = \Drupal::service('plugin.manager.mail');

            $module   = 'partner_dashboard';
            $langcode = \Drupal::currentUser()->getPreferredLangcode();

            // ==========================
            // User Mail
            // ==========================

            $params = [];

            $params['subject'] = 'Thank you for your enquiry';

            $params['body'] = "
            Dear {$form_state->getValue('field_name')},

            Thank you for contacting us.

            We have received your enquiry and our team will contact you shortly.

            Regards,
            MangalPath Team
            ";

                      $mailManager->mail(
                          $module,
                          'user_thank_you',
                         $form_state->getValue('field_email'),
                          $langcode,
                          $params
                      );

                      // ==========================
                      // Admin Mail
                      // ==========================

                      $params = [];

                      $params['subject'] = 'New Enquiry Received';

                      $params['body'] = "New enquiry submitted.

          Name : {$form_state->getValue('field_name')}
          Email : {$form_state->getValue('field_email')}
          Phone : {$form_state->getValue('field_phone')}
          Message : {$form_state->getValue('body')}
          Related: {$related_inquery}
          ";
            $admin_mail = \Drupal::config('system.site')->get('mail');
            $mailManager->mail(
                $module,
                'admin_enquiry',
                $admin_mail,
                $langcode,
                $params
            );


            $notification = \Drupal::service('mangalpath_module.notification');

              $notification->create(

              'New' . $related_inquery . 'enquiry' ,

              $form_state->getValue('field_name').' submitted a'. $related_inquery. 'enquiry.',

              $related_inquery,

              $listing->toUrl()->toString(),

              'node',

              $listing->id(),

              \Drupal::currentUser()->id()

              );

            /*
      |--------------------------------------------------------------------------
      | Decide Enquiry Bundle
      |--------------------------------------------------------------------------
      */

            switch ($bundle) {

                case 'properties_listing_form':

                    $enquiry_bundle = 'property_enquiry_form';
                    break;

                case 'vehicle_sell':

                    if (
                        ! $listing->hasField('field_vehicle_purpose') ||
                        $listing->get('field_vehicle_purpose')->isEmpty()
                    ) {

                        throw new \Exception('Vehicle Purpose not found.');

                    }

                    $term = $listing->get('field_vehicle_purpose')->entity;

                    if (! $term) {

                        throw new \Exception('Vehicle Purpose taxonomy missing.');

                    }

                    $purpose = strtolower(trim($term->label()));

                    switch ($purpose) {

                        case 'sell':
                            $enquiry_bundle = 'vehicle_enquiry_form';
                            break;

                        case 'rent/attach/tour&travel':
                            $enquiry_bundle = 'tour_enquiry_form';
                            break;

                        default:
                            throw new \Exception('Unknown Vehicle Purpose.');

                    }

                    break;

                default:

                    throw new \Exception('Unsupported Listing Type.');

            }

            /*
      |--------------------------------------------------------------------------
      | Duplicate Enquiry Check
      |--------------------------------------------------------------------------
      */

            $query = $this->entityTypeManager
                ->getStorage('node')
                ->getQuery()
                ->condition('type', $enquiry_bundle)
                ->condition('uid', $customer_id)
                ->condition('field_property_id', $listing_id)
                ->accessCheck(false);

            $existing = $query->execute();

            if (! empty($existing)) {

                $this->messenger->addWarning(
                    $this->t('You have already submitted an enquiry for this listing.')
                );

                return;

            }

            /*
      |--------------------------------------------------------------------------
      | Create Enquiry Node
      |--------------------------------------------------------------------------
      */

            $node = Node::create([

                'type'   => $enquiry_bundle,

                'title'  => trim(
                    $form_state->getValue('field_name') .
                    (! empty($form_state->getValue('field_email'))
                            ? ' - ' . $form_state->getValue('field_email')
                            : '')
                ),

                'uid'    => $customer_id,

                'status' => 1,

            ]);
            $node->set(
                'field_name',
                trim($form_state->getValue('field_name'))
            );

            $node->set(
                'field_email',
                trim($form_state->getValue('field_email'))
            );

            $node->set(
                'field_phone_number_data',
                trim($form_state->getValue('field_phone'))
            );

            $node->set(
                'field_property_id',
                $listing_id
            );

            $node->set(
                'field_property_owner_id',
                $partner_id
            );

            $node->set(
                'body',
                [
                    'value'  => trim($form_state->getValue('body')),
                    'format' => 'basic_html',
                ]
            );

            $node->save();

            $this->logger->notice(
                'New enquiry created. Listing ID: @listing | Enquiry ID: @enquiry | Customer ID: @customer | Bundle: @bundle',
                [
                    '@listing'  => $listing_id,
                    '@enquiry'  => $node->id(),
                    '@customer' => $customer_id,
                    '@bundle'   => $enquiry_bundle,
                ]
            );

            $this->messenger->addStatus(
                $this->t('Thank you! Your enquiry has been submitted successfully. Our team will contact you soon.')
            );

            $form_state->setRedirectUrl($listing->toUrl());

        } catch (\Exception $e) {

            $this->logger->error(
                'Enquiry creation failed: @message',
                [
                    '@message' => $e->getMessage(),
                ]
            );

            $this->messenger->addError(
                $this->t('Something went wrong. Please try again later.')
            );

        }

    }
}
