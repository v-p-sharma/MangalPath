<?php

/**
 * @file
 * Contains \Drupal\otp_verification\Form\MiniorangeSettings.
 */

namespace Drupal\otp_verification\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\otp_verification\MiniorangeOTPVerificationCustomer;
use Drupal\user\Entity\User;
use Drupal\otp_verification\MiniorangeOtpUtilities;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\otp_verification\MiniorangeOTPVerificationConstants;
use Drupal\Component\Utility\Unicode;


class MiniorangeValidateUser extends FormBase
{
  public function getFormId()
  {
      return 'miniorange_otp_verification_validate_user';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

      global $base_url, $user, $form, $form_state;

      // DO NOT DELETE
      MiniorangeOtpUtilities::isSessionStarted();
      MiniorangeOtpUtilities::set_otp_cookie('trans_id', $_GET['tx_id']);
	//Dont delete this
    //check if url is hit directly without registration
    /* if (!isset($_SESSION['otp_status']) || !isset($_SESSION['arr2'])) {
       $response = new RedirectResponse($base_url . '/user/register?q=Access Denied.');
       $response->send();
     }*/

    \Drupal::service('page_cache_kill_switch')->trigger();
    $form = self::miniorange_otp_login_build_form($form, $form_state);
    return $form;
  }

  /**
   * Custom form build.
   */
  function miniorange_otp_login_build_form($form, &$form_state, $success_form = TRUE)
  {
      $submit_attributes = array();
      $form = self::miniorange_otp_login_build_form_content($form, $form_state, $success_form);
      $form['loader']['#markup'] = '</div><div class="mo_otp-modal-mo_otp_container mo_otp-modal-footer">';

      $form['actions']['submit'] = array(
          '#type' => 'submit',
          '#value' => 'Validate',
          '#submit' => array('::miniorange_otp_validation_user_validate'),
      );

      $form['actions']['resend'] = array(
          '#type' => 'submit',
          '#value' => t('Resend OTP'),
          '#attributes' => $submit_attributes,
          '#limit_validation_errors' => array(),
          '#submit' => array('::miniorange_otp_resend_otp'),
      );

      $form['actions']['back'] = array(
          '#type' => 'submit',
          '#value' => t('Back'),
          '#attributes' => $submit_attributes,
          '#limit_validation_errors' => array(),
          '#submit' => array('::miniorange_otp_back'),
      );

      return $form;
  }

  function miniorange_otp_login_build_form_content($form, $form_state, $success_form = TRUE)
  {
      return self::miniorange_otp_login_build_otp_validation_form($form, $form_state, $success_form);
  }

  function miniorange_otp_back($form, $form_state)
  {
      global $base_url;
      $response = new RedirectResponse($base_url . '/user/register');
      $response->send();
      exit;
  }

  /**
   * Custom form build with error message.
   */
  function miniorange_otp_build_form_with_error_message(&$form_state)
  {
      $form = array();
      $form = self::miniorange_otp_build_form($form, $form_state, FALSE);
      $form_state['complete form']['header']['#markup'] = $form['header']['#markup'];
      return $form;
  }

  /**
   * Custom validation form build.
   */
  public function miniorange_otp_login_build_otp_validation_form($form, &$form_state, $success_message = TRUE) {
   $config = \Drupal::config('otp_verification.settings');
   $otp_options = $config->get('miniorange_otp_options');

   MiniorangeOtpUtilities::isSessionStarted();
   $otp_sent = MiniorangeOtpUtilities::get_otp_cookie('otp_status');

   if (isset($_GET['otpto']) && $otp_sent == 1) {
      $code = $_GET['otpto'];
      $msg = self::miniorange_email_phone_success_msg($otp_options, $code);
      \Drupal::messenger()->addMessage(t($msg), 'status');
      MiniorangeOtpUtilities::set_otp_cookie('otp_status', 0);
    }

    if ($otp_options == 'both') {
      $form['otp'] = $this->buildOtpField('otp', 'Email OTP', 'Please enter the OTP that has been sent to the registered Email ID.');
      $form['phone_otp'] = $this->buildOtpField('phone_otp', 'Phone OTP', 'Please enter the OTP that has been sent to the Phone No.');
    } elseif ($otp_options == 'email') {
      $form['otp'] = $this->buildOtpField('otp', 'OTP', 'Please enter the OTP that has been sent to the registered Email ID.');
    } elseif ($otp_options == 'phone') {
      $form['phone_otp'] = $this->buildOtpField('phone_otp', 'Phone OTP', 'Please enter the OTP that has been sent to the registered Phone.');
    }

    return $form;
  }

  public function miniorange_email_phone_success_msg($otp_options, $code)
  {
      $config = \Drupal::config('otp_verification.settings');

    $success_msg = '';

    if ($otp_options == 'both') {
      $email_msg = $config->get('miniorange_success_email_otp_message', '');
      $phone_msg = $config->get('miniorange_success_phone_otp_message', '');

      if (!empty($email_msg)) {
        $email_msg = str_replace('##email##', $code, $email_msg);
      } else {
        $email_msg = 'A One Time Passcode has been sent to your email address. Please check your inbox or SPAM folder.';
      }

      if (!empty($phone_msg)) {
        $phone_msg = str_replace('##phone##', $code, $phone_msg);
      } else {
        $phone_msg = 'A One Time Passcode has been sent to your phone number (' . $code . ').';
      }

      $success_msg = $email_msg . ' ' . $phone_msg . ' Please enter the OTP below to verify both.';

    } elseif ($otp_options == 'email') {
      $success_msg = $config->get('miniorange_success_email_otp_message', '');
      if (!empty($success_msg)) {
        $success_msg = str_replace('##email##', $code, $success_msg);
      } else {
        $success_msg = 'A One Time Passcode has been sent to ' . $code . '. Please enter the OTP below to verify your email address. If you cannot see the email in your inbox, make sure to check your SPAM folder.';
      }

    } elseif ($otp_options == 'phone') {
      $success_msg = $config->get('miniorange_success_phone_otp_message', '');
      if (!empty($success_msg)) {
        $success_msg = str_replace('##phone##', $code, $success_msg);
      } else {
        $success_msg = 'A One Time Passcode has been sent to ' . $code . '. Please enter the OTP below to verify your phone number.';
      }
    }

    return $success_msg;
  }


  /**
   * Handling and validating user.
   */
  public function miniorange_otp_validation_user_validate($form, &$form_state)
  {

    $plugin_manager = \Drupal::service('plugin.manager.otp_validation');
    $plugin = $plugin_manager->createInstance('default_otp_validator');
    global $base_url;
    $mail = MiniorangeOtpUtilities::get_otp_cookie('arr2')['mail'];
    $arr2 = MiniorangeOtpUtilities::get_otp_cookie('arr2');
    $valid = $plugin->validate($form, $form_state);
    if($valid){

      $default_role = "authenticated user";
      $users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $arr2['mail']]);
      $existing_user = reset($users);

      if ($existing_user) {

        $existing_user->set('status', 1);
        $existing_user->save();

        user_login_finalize($existing_user);

        _user_mail_notify('register_no_approval_required', $existing_user);

        !empty(\Drupal::config('otp_verification.settings')->get('miniorange_redirect_after_login'))
          ? $edit['redirect'] = \Drupal::config('otp_verification.settings')->get('miniorange_redirect_after_login')
          : $edit['redirect'] = $base_url;

        \Drupal::messenger()->addMessage(t('Registration successful. Now you are logged in.'), 'status');

        $response = new RedirectResponse($edit['redirect']);
        $response->send();
        exit;
      }
    }
    $is_it_updation= MiniorangeOtpUtilities::get_otp_cookie('is_it_updation');

      if(isset($is_it_updation) && $is_it_updation){
        $current_phone_number = MiniorangeOtpUtilities::get_otp_cookie('current_phone_number');
        $account=\Drupal::currentUser();
        $current_user = \Drupal::currentUser();
        $uid = $current_user->id();
        $account = User::load($account->id());
        $account->field_phone_number = $current_phone_number;
        $account->save();
        \Drupal::messenger()->addMessage(t('The changes have been saved.'), 'status');
        $response = new RedirectResponse($base_url . '/user/'.$uid.'/edit');
        $response->send();
        exit;
      }

    elseif($valid == FALSE){
        \Drupal::messenger()->addMessage(t('OTP entered is incorrect. Please enter valid OTP.'), 'error');
    }

//    $config = \Drupal::config('otp_verification.settings');
//    $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');
//
//    MiniorangeOtpUtilities::isSessionStarted();
//
//    $otp_options = $config->get('miniorange_otp_options');
//
//    $mail = MiniorangeOtpUtilities::get_otp_cookie('arr2')['mail'];
//
//    $otp_token = $form_state->getValue('otp');
//    $otp_token_phone = $form_state->getValue('phone_otp');
//
//    $otp_token_phone = trim($otp_token_phone);
//    $otp_token = trim($otp_token);
//
//    $trans_id = MiniorangeOtpUtilities::get_otp_cookie('trans_id');
//    $trans_id = base64_decode($trans_id);
//
//    //\Drupal::messenger()->deleteAll();
//
//    if (!MiniorangeOtpUtilities::isCurlInstalled())
//    {
//      return json_encode(array(
//        "status" => 'CURL_ERROR',
//        "statusMessage" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
//      ));
//    }
//
//    $url = MiniorangeOTPVerificationConstants::BASE_URL . '/moas/api/auth/validate';
//
//    global $base_url;
//    $customer_key = $config->get('miniorange_otp_verification_customer_id');
//    $api_key = $config->get('miniorange_otp_verification_customer_api_key');
//     $otp_status = '';
//    if ($otp_options == 'both') {
//
//      $fields['token'] = $otp_token;
//      $fields['txId'] = $trans_id;
//      $fields['customerKey'] = $customer_key;
//      $fields['username'] = $mail;
//      $fields['authType'] = 'EMAIL';
//
//      $mo_otp_verification_customer = new MiniorangeOTPVerificationCustomer(null, null, null, null, $customer_key, $api_key);
//      $content = $mo_otp_verification_customer->callService($url, $fields, true);
//      $db_var->set('miniorange_otp_token', $otp_token)->save();
//      $otp_status = json_decode($content)->status;
//
//      $fields_phone['token'] = $otp_token_phone;
//      $fields_phone['txId'] = $trans_id;
//      $fields_phone['customerKey'] = $customer_key;
//      $fields_phone['username'] = $mail;
//      $fields_phone['authType'] = 'PHONE';
//      $mo_otp_verification_customer = new MiniorangeOTPVerificationCustomer(null, null, null, null, $customer_key, $api_key);
//      $content_phone = $mo_otp_verification_customer->callService($url, $fields_phone, true);
//      $db_var->set('miniorange_otp_token_phone', $otp_token_phone)->save();
//      $otp_status_phone = json_decode($content_phone)->status;
//    } elseif ($otp_options == 'phone') {
//      $fields['token'] = $otp_token_phone;
//      $fields['txId'] = $trans_id;
//      $fields['customerKey'] = $customer_key;
//      $fields['username'] = $mail;
//      $fields['authType'] = 'PHONE';
//
//      $mo_otp_verification_customer = new MiniorangeOTPVerificationCustomer(null, null, null, null, $customer_key, $api_key);
//      $content_phone = $mo_otp_verification_customer->callService($url, $fields, true);
//      $db_var->set('miniorange_otp_token_phone', $otp_token_phone)->save();
//      $otp_status_phone = json_decode($content_phone)->status;
//    } else {
//      $fields['token'] = $otp_token;
//      $fields['txId'] = $trans_id;
//      $fields['customerKey'] = $customer_key;
//      $fields['username'] = $mail;
//      $fields['authType'] = 'EMAIL';
//
//      $mo_otp_verification_customer = new MiniorangeOTPVerificationCustomer(null, null, null, null, $customer_key, $api_key);
//      $content_phone = $mo_otp_verification_customer->callService($url, $fields, true);
//      $db_var->set('miniorange_otp_token', $otp_token)->save();
//      $otp_status = json_decode($content_phone)->status;
//    }
//
//    \Drupal::messenger()->deleteAll();
//
//    if ($otp_status == "SUCCESS" || $otp_status_phone == "SUCCESS") {
//      $arr2 = MiniorangeOtpUtilities::get_otp_cookie('arr2');
//      $default_role = "authenticated user";
//
//      $users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $arr2['mail']]);
//      $existing_user = reset($users);
//
//      if ($existing_user) {
//
//        $existing_user->set('status', 1);
//        $existing_user->save();
//
//        user_login_finalize($existing_user);
//
//        _user_mail_notify('register_no_approval_required', $existing_user);
//
//        !empty(\Drupal::config('otp_verification.settings')->get('miniorange_redirect_after_login'))
//          ? $edit['redirect'] = \Drupal::config('otp_verification.settings')->get('miniorange_redirect_after_login')
//          : $edit['redirect'] = $base_url;
//
//        \Drupal::messenger()->addMessage(t('Registration successful. Now you are logged in.'), 'status');
//
//        $response = new RedirectResponse($edit['redirect']);
//        $response->send();
//        exit;
//      }
//
//      if ($otp_options == 'phone'){
//        $machine_name_of_phone_field = $config->get('machine_name_of_phone_field');
//        $new_user[$machine_name_of_phone_field] = MiniorangeOtpUtilities::getNestedValue($arr2[$machine_name_of_phone_field]);
//      }
//
//      $is_it_updation= MiniorangeOtpUtilities::get_otp_cookie('is_it_updation');
//      if(isset($is_it_updation) && $is_it_updation){
//
//        $current_phone_number = MiniorangeOtpUtilities::get_otp_cookie('current_phone_number');
//        $account=\Drupal::currentUser();
//        $current_user = \Drupal::currentUser();
//        $uid = $current_user->id();
//        $account = User::load($account->id());
//        $account->field_phone_number = $current_phone_number;
//        $account->save();
//        \Drupal::messenger()->addMessage(t('The changes have been saved.'), 'status');
//        $response = new RedirectResponse($base_url . '/user/'.$uid.'/edit');
//        $response->send();
//        exit;
//      }
//
//    }
//    elseif ($otp_status == "ERROR" || $otp_status_phone == "ERROR")
//    {
//      $invalid_otp_msg = $config->get('miniorange_invalid_otp_message', '');
//      if (!empty($invalid_otp_msg))
//        \Drupal::messenger()->addMessage(t($invalid_otp_msg), 'error');
//      else
//        \Drupal::messenger()->addMessage(t('OTP entered is incorrect. Please enter valid OTP.'), 'error');
//    }
  }
  /**
   * Resend OTP
   */
  function miniorange_otp_resend_otp(&$form, $form_state)
  {
      global $_miniorange_otp_x;

      MiniorangeOtpUtilities::isSessionStarted();

      $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');
      $config = \Drupal::config('otp_verification.settings');
      $user_values = MiniorangeOtpUtilities::get_otp_cookie('arr2');
      $umail = $user_values['mail'];

      $otp_options_email = $config->get('miniorange_otp_options_email');
      \Drupal::messenger()->deleteAll();

      if ($otp_options_email) {
          $emailcount = mb_strlen($umail);
          $emailc1 = mb_substr($umail, 0, 3);
          $emailc2 = mb_substr($umail, $emailcount - 4, $emailcount);

          for ($i = 4; $i <= $emailcount - 4; $i++)
          {
              $_miniorange_otp_x = "X" . $_miniorange_otp_x;
          }

          $email_code = $emailc1 . $_miniorange_otp_x . $emailc2;
          if (!MiniorangeOtpUtilities::isCurlInstalled())
          {
              return json_encode(array(
                  "status" => 'CURL_ERROR',
                  "statusMessage" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
              ));
          }
          $url = MiniorangeOTPVerificationConstants::BASE_URL . '/moas/api/auth/challenge';
          $customer_key = $config->get('miniorange_otp_verification_customer_id');
          $api_key = $config->get('miniorange_otp_verification_customer_api_key');

          $fields = array(
              'customerKey' => $customer_key,
              'email' => $umail,
              'authType' => 'EMAIL',
              'transactionName' => 'Drupal OTP Verification',
          );

          $mo_otp_verification_customer = new MiniorangeOTPVerificationCustomer(null,null,null,null,$customer_key, $api_key);
          $content = $mo_otp_verification_customer->callService($url, $fields,true);

          $trans_status = json_decode($content)->status;

          if ($trans_status == "SUCCESS") {
              $tx_id = json_decode($content)->txId;
              MiniorangeOtpUtilities::set_otp_cookie('trans_id', $tx_id);
              \Drupal::messenger()->addMessage(t('An OTP has been resent to @otp_resend', array('@otp_resend' => $email_code)), 'status');
          } else {
              $error_phone_msg = $config->get('miniorange_error_email_otp_message', '');
              if (!empty($error_phone_msg))
                  \Drupal::messenger()->addMessage(t($error_phone_msg), 'error');
              else
                  \Drupal::messenger()->addMessage(t('There was an error in sending the OTP.'), 'error');
          }
      }
      else
      {
          $ph = MiniorangeOtpUtilities::get_otp_cookie('phone_during_register');
          $phno = mb_strlen($ph);
          $phbr1 = mb_substr($ph, 0, 4);
          $phbr2 = mb_substr($ph, $phno - 2, $phno);

          for ($i = 5; $i <= $phno - 2; $i++)
          {
              $_miniorange_otp_x = "X" . $_miniorange_otp_x;
          }

          $ph_code = $phbr1 . $_miniorange_otp_x . $phbr2;

          if (!MiniorangeOtpUtilities::isCurlInstalled()) {
              return json_encode(array(
                  "status" => 'CURL_ERROR',
                  "statusMessage" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
              ));
          }

          $url = MiniorangeOTPVerificationConstants::BASE_URL . '/moas/api/auth/challenge';
          $customer_key = $config->get('miniorange_otp_verification_customer_id');
          $api_key = $config->get('miniorange_otp_verification_customer_api_key');

          $fields = array(
              'customerKey' => $customer_key,
              'phone' => $ph,
              'authType' => 'SMS',
              'transactionName' => 'Drupal OTP Verification',
          );

          $mo_otp_verification_customer = new MiniorangeOTPVerificationCustomer(null,null,null,null, $customer_key, $api_key);
          $content = $mo_otp_verification_customer->callService($url, $fields,true);

          $trans_status = json_decode($content)->status;

          if ($trans_status == "SUCCESS") {
              $tx_id = json_decode($content)->txId;
              MiniorangeOtpUtilities::set_otp_cookie('trans_id', $tx_id);
              \Drupal::messenger()->addMessage(t('OTP has been resent to @otp_resend_phone', array('@otp_resend_phone' => $ph_code)), 'status');
          }
          else
          {
              $error_phone_msg = $config->get('miniorange_error_phone_otp_message', '');
              if (!empty($error_phone_msg))
                  \Drupal::messenger()->addMessage(t($error_phone_msg), 'error');
              else
                  \Drupal::messenger()->addMessage(t('There was an error in sending the OTP.'), 'error');
          }
      }
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

  }
  private function buildOtpField($type, $title, $desc) {
    return [
      '#name' => $type,
      '#type' => 'textfield',
      '#attributes' => ['autofocus' => 'autofocus'],
      '#title' => t($title),
      '#default_value' => '',
      '#size' => 60,
      '#description' => t($desc),
      '#maxlength' => 15,
      '#required' => TRUE,
    ];
  }
}
