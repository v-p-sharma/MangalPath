<?php

/**
 * @file
 * Contains \Drupal\otp_verification\Form\MiniorangeOTPVerificationCustomerSetup.
 */

namespace Drupal\otp_verification\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\otp_verification\MiniorangeOtpUtilities;
use Drupal\otp_verification\MiniorangeOTPVerificationCustomer;
use Drupal\otp_verification\MiniorangeOTPVerificationConstants;


class MiniorangeOTPVerificationCustomerSetup extends FormBase {
  public function getFormId()
  {
    return 'miniorange_otp_verification_customer_setup';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    global $base_url;
    $config = \Drupal::config('otp_verification.settings');

    $form['markup_library'] = array(
      '#attached' => array(
        'library' => array(
          "otp_verification/otp_verification.admin",
          'otp_verification/otp_verification.phone',
          'core/drupal.dialog.ajax',
        )
      ),
    );

    $current_status = $config->get('miniorange_otp_verification_status');

    if ($current_status == 'PLUGIN_CONFIGURATION') {

      $form['markup_top_message'] = array(
        '#markup' => '<div class="mo_otp_verification_table_layout_1"><div class="mo_otp_verification_table_layout mo_otp_container">'
      );

      $form['markupboit_message'] = array(
        '#markup' => '<div class="mo_otp_verification_welcome_message">Thank you for registering with miniOrange</div></br><h4>Your Profile: </h4>'
      );

      $header = array(
        ['data' => t('ATTRIBUTE')],
        ['data' => t('VALUE')],
      );

      $options = array(
        ['Customer Email', $config->get('miniorange_otp_verification_customer_admin_email')],
        ['Customer ID', $config->get('miniorange_otp_verification_customer_id')],
        //['Token Key', $config->get('miniorange_otp_verification_customer_admin_token')],
        //['API Key', $config->get('miniorange_otp_verification_customer_api_key')],
        ['Drupal Version', \Drupal::VERSION],
        ['PHP Version', phpversion()],
      );

      $form['fieldset']['customerinfo'] = array(
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $options,
      );

      $form['markup_1'] = array(
        '#markup' => t('<br><h6>Track your remaining transactions:</h6>
          1. Click on the button below.<br>
          2. Login using the credentials you used to register for this module.<br>
          3. You will be presented with <b><i>View Transactions</i></b> page.<br>
          4. From this page you can track your remaining transactions<br><br>'),
      );

      $user_email = $config->get('miniorange_otp_verification_customer_admin_email');
      $transactionURL = MiniorangeOTPVerificationConstants::BASE_URL . '/moas/login?username=' . $user_email . '&redirectUrl='. MiniorangeOTPVerificationConstants::BASE_URL .'/moas/viewtransactions';

        $form['overview_view_transaction_button'] = [
            '#type' => 'link',
            '#title' => t('View Transactions'),
            '#url' => Url::fromUri($transactionURL),
            '#attributes' => ['class' => ['button', 'button--primary'], 'target' => '_blank'],
        ];

      $form['markup_2']['miniorage_remove_account'] = array(
        '#type' => 'link',
        '#title' => $this->t('Remove Account'),
        '#url' => Url::fromRoute('otp_verification.modal_form'),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'button',
          ],
        ],
        '#suffix' => '<br/><br/></div>',
      );

      MiniorangeOtpUtilities::Two_FA_Advertisement($form, $form_state);

      return $form;
    }

    $url = $base_url . '/admin/config/people/otp_verification/customer_setup';

    $form['header_top_style_1'] = array('#markup' => '<div class="mo_otp_verification_table_layout_1">',);

    $form['markup_top'] = array(
      '#markup' => '<div class="mo_otp_container">'
    );

    $form['markup_15'] = array(
      '#markup' => '<h2>Logins</h2>',
    );

    $form['markup_16'] = array(
      '#markup' => '<div class="mo_saml_highlight_background_note_2" style="">Please login with your miniorange account.</b></div><hr>'
    );

    $form['Mo_auth_customer_login_username'] = array(
      '#type' => 'email',
      '#title' => t('Email <span style="color: red">*</span>'),
      '#attributes' => array('style' => 'width:50%'),
    );

    $form['Mo_auth_customer_login_password'] = array(
      '#type' => 'password',
      '#title' => t('Password <span style="color: red">*</span>'),
      '#attributes' => array('style' => 'width:50%'),
    );

    $form['Mo_auth_customer_login_button'] = array(
      '#type' => 'submit',
      '#value' => t('Login'),
      '#button_type' => 'primary',
      '#limit_validation_errors' => array(),
      '#prefix' => '<div class="otp_row"><div class="otp_name">',
      '#suffix' => '</div>'
    );

    $form['register_link'] = array(
      '#markup' => '<a href="https://www.miniorange.com/businessfreetrial" target="_blank" class="button">Create an account?</a>',
      '#prefix' => '<div class="otp_value">',
      '#suffix' => '</div></div></div>'
    );
    

    MiniorangeOtpUtilities::Two_FA_Advertisement($form, $form_state);

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');

    $phone = '';

    $username = trim($form['Mo_auth_customer_login_username']['#value']);
    $password = trim($form['Mo_auth_customer_login_password']['#value']);
    

    if (empty($username) || empty($password)) {
      \Drupal::messenger()->addError(t('The <b><u>Email </u></b> and <b><u>Password</u></b> fields are mandatory.'));
      return;
    }

    if (!\Drupal::service('email.validator')->isValid($username)) {
      \Drupal::messenger()->addError(t('The email address <i>' . $username . '</i> is not valid.'));
      return;
    }

    $customer_config = new MiniorangeOTPVerificationCustomer($username, $phone, $password, NULL);
    $check_customer_response = json_decode($customer_config->checkCustomer());

    if ($check_customer_response->status == 'CUSTOMER_NOT_FOUND') {
      \Drupal::messenger()->addError(t('The account with username <i>'.$username.'</i> does not exist.'));
        return;
    } elseif ($check_customer_response->status == 'TRANSACTION_LIMIT_EXCEEDED') {
      \Drupal::messenger()->addError(t('An error has been occured while processing your request. Please try after some time.'));
    } elseif ($check_customer_response->status == 'CURL_ERROR') {
      \Drupal::messenger()->addError(t('cURL is not enabled. Please enable cURL.'));
    } else {
      $customer_keys_response = json_decode($customer_config->getCustomerKeys());

      if (json_last_error() == JSON_ERROR_NONE) {

        $current_status = 'PLUGIN_CONFIGURATION';

        $db_var->set('miniorange_otp_verification_customer_id', $customer_keys_response->id)
          ->set('miniorange_otp_verification_customer_admin_token', $customer_keys_response->token)
          ->set('miniorange_otp_verification_customer_admin_email', $username)
          ->set('miniorange_otp_verification_customer_admin_phone', $phone)
          ->set('miniorange_otp_verification_customer_api_key', $customer_keys_response->apiKey)
          ->set('miniorange_otp_verification_status', $current_status)
          ->save();

        \Drupal::messenger()->addStatus(t('Successfully retrieved your account.'));
      } else {
        \Drupal::messenger()->addError(t('Invalid credentials.'));
      }
    }
  }
}
