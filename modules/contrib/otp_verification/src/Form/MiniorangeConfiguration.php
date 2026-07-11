<?php

/**
 * @file
 * Contains \Drupal\otp_verification\Form\MiniorangeGeneralSettings.
 */

namespace Drupal\otp_verification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\otp_verification\MiniorangeOtpCustomer;
use Drupal\otp_verification\MiniorangeOtpUtilities;
use \Drupal\otp_verification\MiniorangeOTPVerificationConstants;

class MiniorangeConfiguration extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'miniorange_mapping';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    global $base_url, $_miniorange_otp_msg;
    $config = \Drupal::config('otp_verification.settings');

    $isCustomerRegisterd = !MiniorangeOtpUtilities::isCustomerRegistered();
    $form['markup_library'] = array(
      '#attached' => array(
        'library' => array(
          "otp_verification/otp_verification.admin",
            'core/drupal.dialog.ajax',
        )
      ),
    );

    if (!MiniorangeOtpUtilities::isCustomerRegistered()) {
      $register_url = $base_url . '/admin/config/people/otp_verification/customer_setup';
      $form['header'] = array(
        '#markup' => '<div class="mo_saml_configure_message">You need to <a href="' . $register_url . '" >register/login</a> with miniOrange before using this module.</div>',
      );

    }

    self::miniorange_otp_handle_mo_check_ln($_miniorange_otp_msg);

    $var = $config->get('mo_customer_check_ln');
    $admin_email = $config->get('miniorange_otp_verification_customer_admin_email');

    $otp_length_config = MiniorangeOtpVerificationConstants::BASE_URL . '/moas/login?username=' . $admin_email . '&redirectUrl=' . MiniorangeOtpVerificationConstants::BASE_URL . '/moas/admin/customer/customerpreferences';

    $pcode = MiniorangeOtpCustomer::$pcode;
    $bcode = MiniorangeOtpCustomer::$bcode;

    $form['markup_1'] = array(
      '#markup' => '<div class="mo_otp_verification_table_layout_1"><div class="mo_otp_verification_table_layout mo_otp_container">
                        <h3>SMS & EMAIL CONFIGURATION</h3><hr><hr><br/>',
    );

    $form['markup_headLine'] = array(
      '#markup' => '<div class="mo_otp_verification_highlight_background_note"><b>Look at the sections below to customize the Email and SMS that you receive:</b></div><br>'
    );

    $mo_server_url = MiniorangeOtpVerificationConstants::BASE_URL . '/moas/login?username=' . $admin_email . '&redirectUrl='. MiniorangeOtpVerificationConstants::BASE_URL. '/moas/admin/customer';
    if (MiniorangeOtpUtilities::isCustomerRegistered()) {
      $custom_sms = $mo_server_url  . '/showsmstemplate';
      $custom_sms_gateway = $mo_server_url . '/smsconfig';
      $custom_email = $mo_server_url . '/emailtemplateconfiguration';
      $custom_email_gateway = $mo_server_url . '/configureSMTP';
      $targetBlank = 'target="_blank"';
    } else {
      $custom_sms = $base_url . '/admin/config/people/otp_verification/customer_setup';
      $custom_email = $base_url . '/admin/config/people/otp_verification/customer_setup';
      $targetBlank = '';
    }

    $form['markup_2'] = array(
      '#markup' => '1. <a href="' . $custom_sms . '" ' . $targetBlank . '>Custom SMS Template</a> : Change the text of the message that you receive on your phones.<br>'
        . '2. <a href="' . $custom_email . '" ' . $targetBlank . '>Custom Email Template</a> : Change the text of the email that you receive.<br>'
    );

    $form['markup_3'] = array(
      '#markup' => '<div class="mo_otp_verification_highlight_background_note"><b> How can I change the SenderID/Number of the SMS I receive?</b></div>'
        . '<div id="q1" class="" ><ol><li>SenderID/Number is gateway specific. You will need to use your own SMS gateway for this.</li></ol></div><br>',
    );

    $form['markup_4'] = array(
      '#markup' => '<div class="mo_otp_verification_highlight_background_note"><b>How can I change the Sender Email of the Email I receive?</b><br></div>'
        . '<div id="q2" class="" ><ol><li>Sender Email is gateway specific. You will need to use your own Email gateway for this.</li></ol></div><br>',
    );

    $url = $base_url . '/admin/config/people/otp_verification/Licensing';
    $desable_feature = "";
    $premium_feature = "";
    if ($var != $pcode && $var != $bcode) {
      $desable_feature = 'disabled = "true"';
      $premium_feature = '[Premium Feature]';
    }

    $form['otp_length'] = array(
      '#markup' => '<br><h3>OTP PREFERENCES</h3><hr><hr><br/>',
    );

    $form['markup_headLine1'] = array(
      '#markup' => '<div class="mo_otp_verification_highlight_background_note"><b>Look at the sections below to customize the OTP that you receive:</b></div><br>'
    );

    $form['otp_length_custom1'] = array(
      '#markup' => '1. <a href="' . $otp_length_config . '" target="_blank">Custom OTP length</a> : You can configure settings to use custom OTP length.<br>',
    );

    $form['otp_length_custom2'] = array(
      '#markup' => '2. <a href="' . $otp_length_config . '" target="_blank">Custom OTP Validity</a> (in mins) : You can configure settings to use custom OTP Validity (in mins).<br><br>',
    );

    $form['redirect_url'] = array(
      '#markup' => '<br><h3>Redirect URL</h3>',
    );

    $form['otp_logout_url'] = array(
      '#type' => 'textfield',
      '#disabled' => $isCustomerRegisterd,
      '#title' => t('Default Redirect URL after logout'),
      '#default_value' => \Drupal::config('otp_verification.settings')->get('otp_logout_url'),
      '#attributes' => array('style' => 'width:73%','placeholder' => 'Enter Default Redirect URL'),
      '#prefix' => '<hr><hr><br>'
    );

    $form['otp_login_url'] = array(
      '#type' => 'textfield',
      '#disabled' => $isCustomerRegisterd,
      '#title' => t('Default Redirect URL after Registration'),
      '#default_value' =>\Drupal::config('otp_verification.settings')->get('otp_login_url'),
      '#attributes' => array('style' => 'width:73%','placeholder' => 'Enter Default Redirect URL'),
      '#suffix' => '<br>',
    );

    $form['miniorange_otp_config_save_button'] = array(
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => t('Save'),
        '#submit' => array('::miniorange_otp_config_save'),
        '#disabled' => $isCustomerRegisterd,
        '#suffix' => '</div>'
    );

    MiniorangeOtpUtilities::Two_FA_Advertisement($form, $form_state);

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

  }

  /**
   * Handling Save Config tab.
   */
  function miniorange_otp_config_save($form, &$form_state)
  {
    global $base_url;
    $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');

    $otp_logout_url = trim($form['otp_logout_url']['#value']);
    $otp_login_url = trim($form['otp_login_url']['#value']);

    $db_var->set('otp_logout_url', $otp_logout_url)
      ->set('otp_login_url', $otp_login_url)
      ->save();

    \Drupal::messenger()->addMessage(t('Configurations saved successfully.'), 'status');
  }

  /**
   * Check license.
   */
  public function miniorange_otp_handle_mo_check_ln($showmessage)
  {

    global $base_url;
    $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');

    if (!MiniorangeOtpUtilities::isCustomerRegistered()){
      return;
    }
    $email = $db_var->get('miniorange_otp_verification_customer_admin_email');
    $phone = $db_var->get('miniorange_otp_verification_customer_admin_phone');
    $challenge_otp = new MiniorangeOtpCustomer($email, $phone, NULL, NULL);
    $content = json_decode($challenge_otp->checkCustomerLn(), TRUE);

    $license_plan = isset($content['licensePlan']) ? $content['licensePlan'] : '';
    $current_status = '';
    $licensing = $base_url.'/admin/config/people/otp_verification/Licensing';

    if (strcasecmp($content['status'], 'SUCCESS') == 0) {

      array_key_exists('licensePlan', $content) && !MiniorangeOtpCustomer::moCheckEmptyOrNull($content['licensePlan']) ? $db_var->set('mo_customer_check_ln', base64_encode($content['licensePlan']))->save() : $db_var->set('mo_customer_check_ln', '')->save();

      if ($showmessage) {

        array_key_exists('licensePlan', $content) && !MiniorangeOtpCustomer::moCheckEmptyOrNull($content['licensePlan']) ? \Drupal::messenger()->addMessage(t('Thank you, you have been upgraded to @license_name', array('@license_name' => $license_plan)), 'status') : \Drupal::messenger()->addMessage(t('You are on our FREE plan, check <a href="' . $licensing . '" >Licensing</a> tab to learn how to upgrade.'), 'status');

        if (array_key_exists('licensePlan', $content) && !MiniorangeOtpCustomer::moCheckEmptyOrNull($content['licensePlan'])) {
          $db_var->set('mo_customer_email_transactions_remaining', $current_status)
            ->set('mo_customer_phone_transactions_remaining', $current_status)
            ->set('mo_otp_plugin_version', $current_status)
            ->set('mo_customer_validation_transaction_message', $current_status)
            ->save();
        }
      }
    } elseif (strcasecmp($content['status'], 'FAILED') == 0) {
      array_key_exists('licensePlan', $content) && !MiniorangeOtpCustomer::moCheckEmptyOrNull($content['licensePlan']) ? \Drupal::messenger()->addMessage(t('Thank you, you have been upgraded to @license_name', array('@license_name' => $license_plan)), 'status') : \Drupal::messenger()->addMessage(t('You are on our FREE plan, check <a href="' . $licensing . '" >Licensing</a> tab to learn how to upgrade.'), 'status');
    }
  }
}
