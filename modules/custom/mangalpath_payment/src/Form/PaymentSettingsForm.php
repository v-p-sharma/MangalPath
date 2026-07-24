<?php

namespace Drupal\mangalpath_payment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class PaymentSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {

    return [
      'mangalpath_payment.settings',
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {

    return 'mangalpath_payment_settings_form';

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $config = $this->config('mangalpath_payment.settings');

    $form['razorpay'] = [
      '#type' => 'details',
      '#title' => $this->t('Razorpay Settings'),
      '#open' => TRUE,
    ];

    $form['razorpay']['key_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key ID'),
      '#default_value' => $config->get('key_id'),
      '#required' => TRUE,
      '#disabled' => !empty($GLOBALS['settings']['mangalpath_payment']['key_id']),
    ];

    $form['razorpay']['key_secret'] = [
  '#type' => 'password',
  '#title' => $this->t('Key Secret'),
  '#description' => $this->t('Leave empty to keep existing secret.'),
  '#disabled' => !empty($GLOBALS['settings']['mangalpath_payment']['key_secret']),
];

    $form['razorpay']['webhook_secret'] = [
  '#type' => 'password',
  '#title' => $this->t('Webhook Secret'),
  '#description' => $this->t('Leave empty to keep existing webhook secret.'),
  '#disabled' => !empty($GLOBALS['settings']['mangalpath_payment']['webhook_secret']),
];

    $form['payment'] = [
      '#type' => 'details',
      '#title' => $this->t('Payment Configuration'),
      '#open' => TRUE,
    ];

    $form['payment']['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Payment Mode'),
      '#options' => [
        'test' => 'Test',
        'live' => 'Live',
      ],
      '#default_value' => $config->get('mode') ?: 'test',
    ];

    $form['payment']['currency'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Currency'),
      '#default_value' => $config->get('currency') ?: 'INR',
      '#required' => TRUE,
    ];

    $form['payment']['listing_amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Listing Amount'),
      '#default_value' => $config->get('listing_amount') ?: 99,
      '#min' => 1,
      '#step' => 1,
      '#required' => TRUE,
      '#field_suffix' => 'INR',
    ];
    $form['payment']['test_connection'] = [
  '#type' => 'submit',
  '#value' => $this->t('Test Razorpay Connection'),
  '#submit' => ['::testConnection'],
  '#limit_validation_errors' => [],
];
    return parent::buildForm($form, $form_state);

  }
  /**
 * Test Razorpay API connection.
 */
public function testConnection(array &$form, FormStateInterface $form_state): void {

  try {

    $api = new \Razorpay\Api\Api(
      $form_state->getValue('key_id'),
      $form_state->getValue('key_secret')
    );

    $api->order->all([
      'count' => 1,
    ]);

    $this->messenger()->addStatus(
      $this->t('Razorpay connection successful.')
    );

  }
  catch (\Exception $exception) {

    $this->messenger()->addError(
      $this->t('Connection failed: @message', [
        '@message' => $exception->getMessage(),
      ])
    );

  }

}


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {

    if ($form_state->getValue('listing_amount') <= 0) {
      $form_state->setErrorByName(
        'listing_amount',
        $this->t('Amount must be greater than zero.')
      );
      if (!preg_match('/^[A-Z]{3}$/', strtoupper($form_state->getValue('currency')))) {

  $form_state->setErrorByName(
    'currency',
    $this->t('Currency must be a 3-letter ISO code (example: INR, USD).')
  );

}
    }

    parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
 public function submitForm(array &$form, FormStateInterface $form_state): void {

  $config = $this->configFactory
    ->getEditable('mangalpath_payment.settings');

  $config->set(
    'key_id',
    trim($form_state->getValue('key_id'))
  );

  if (!empty($form_state->getValue('key_secret'))) {

    $config->set(
      'key_secret',
      trim($form_state->getValue('key_secret'))
    );

  }

  if (!empty($form_state->getValue('webhook_secret'))) {

    $config->set(
      'webhook_secret',
      trim($form_state->getValue('webhook_secret'))
    );

  }

  $config->set(
    'mode',
    $form_state->getValue('mode')
  );

  $config->set(
    'currency',
    strtoupper(trim($form_state->getValue('currency')))
  );

  $config->set(
    'listing_amount',
    (float) $form_state->getValue('listing_amount')
  );

  $config->save();

  parent::submitForm($form, $form_state);

}

}