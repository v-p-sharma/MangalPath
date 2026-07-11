<?php

/**
 * @file
 * Contains \Drupal\otp_verification\Form\MiniorangeCustomTemplates.
 */

namespace Drupal\otp_verification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\otp_verification\MiniorangeOtpUtilities;

class MiniorangeCustomTemplates extends FormBase
{

  public function getFormId()
  {
    return 'miniorange_custom_templates';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $config = \Drupal::config('otp_verification.settings');
    global $base_url;
    $isCustomerRegisterd = !MiniorangeOtpUtilities::isCustomerRegistered();

    $form['markup_library'] = array(
      '#attached' => array(
        'library' => array(
          "otp_verification/otp_verification.admin",
          'core/drupal.dialog.ajax',
        )
      ),
    );

    if ($isCustomerRegisterd) {
      $register_url = $base_url . '/admin/config/people/otp_verification/customer_setup';
      $form['header'] = array(
        '#markup' => '<div class="mo_saml_configure_message">You need to <a href="' . $register_url . '" >register/login</a> with miniOrange before using this module.</div><br>',
      );
    }

    $form['markup_1'] = array(
      '#markup' => '<div class="mo_otp_verification_table_layout_1"><div class="mo_otp_verification_table_layout mo_otp_container templates">
                        <h3>EMAIL MESSAGES</h3><hr><hr><br/>',
    );

    $form['miniorange_success_email_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => t('<b>SUCCESS OTP MESSAGE</b>'),
      '#default_value' => !empty($config->get('miniorange_success_email_otp_message', '')) ? $config->get('miniorange_success_email_otp_message') : t('A One Time Passcode has been sent to ##email##. Please enter the OTP below to verify your email address. If you cannot see the email in your inbox, make sure to check your SPAM folder.'),
      '#description' => t('<b>Note: </b>##email## in the message body will be replaced by the user email address.'),
    );

    $form['miniorange_error_email_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => t('<b>ERROR OTP MESSAGE</b>'),
      '#default_value' => !empty($config->get('miniorange_error_email_otp_message', '')) ? $config->get('miniorange_error_email_otp_message') : t('There was an error in sending the OTP.'),
    );

    $form['miniorange_blocked_email_message'] = array(
      '#type' => 'textarea',
      '#title' => t('<b>BLOCKED EMAIL MESSAGE</b>'),
      '#default_value' => !empty($config->get('miniorange_blocked_email_message', '')) ? $config->get('miniorange_blocked_email_message') : t('You are not allowed to register. Please contact your site administrator. Your domain may be blocked by admin.'),
      '#suffix' => '<br><h3>COMMON MESSAGES</h3><hr><hr><br>',
    );

    $form['miniorange_invalid_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => t('<b>INVALID OTP MESSAGE</b>'),
      '#default_value' => !empty($config->get('miniorange_invalid_otp_message', '')) ? $config->get('miniorange_invalid_otp_message') : t('OTP entered is incorrect. Please enter valid OTP.'),
      '#suffix' => '<br>',
    );

    $form['miniorange_email_msg_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save Email Template'),
      '#submit' => array('::miniorange_email_template_submit'),
      '#disabled' => $isCustomerRegisterd,
    );

    $form['markup_2'] = array(
      '#markup' => '</div><div class="mo_otp_verification_table_layout_support_1 mo_otp_verification_container templates"><h3>SMS/MOBILE MESSAGES</h3><hr><hr><br/>'
    );

    $form['miniorange_success_phone_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => t('<b>SUCCESS OTP MESSAGE</b>'),
      '#default_value' => !empty($config->get('miniorange_success_phone_otp_message', '')) ? $config->get('miniorange_success_phone_otp_message') : t('A One Time Passcode has been sent to ##phone##. Please enter the OTP below to verify your phone number.'),
      '#description' => t('<b>Note: </b>##phone## in the message body will be replaced by the user phone number.'),
    );

    $form['miniorange_error_phone_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => t('<b>ERROR OTP MESSAGE</b>'),
      '#default_value' => !empty($config->get('miniorange_error_phone_otp_message', '')) ? $config->get('miniorange_error_phone_otp_message') : t('There was an error in sending the OTP.'),
    );

    $form['miniorange_blocked_phone_message'] = array(
      '#type' => 'textarea',
      '#title' => t('<b>BLOCKED COUNTRY CODE MESSAGE</b>'),
      '#default_value' => !empty($config->get('miniorange_blocked_phone_message', '')) ? $config->get('miniorange_blocked_phone_message') : t('You are not allowed to register. Please contact your site administrator. Your country code may be blocked by admin.'),
    );

    $form['miniorange_invalid_format_message'] = array(
      '#type' => 'textarea',
      '#title' => t('<b>INVALID PHONE NUMBER FORMAT MESSAGE</b>'),
      '#default_value' => !empty($config->get('miniorange_invalid_format_message', '')) ? $config->get('miniorange_invalid_format_message') : t('Enter a valid phone number.'),
      '#suffix' => '<br>',
    );

    $form['miniorange_phone_msg_submit'] = array(
      '#type' => 'submit',

      '#value' => t('Save Phone Template'),
      '#submit' => array('::miniorange_phone_template_submit'),
      '#disabled' => $isCustomerRegisterd,
    );
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

  }

  public function miniorange_email_template_submit(array &$form, FormStateInterface $form_state)
  {

    $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');

    $success_message = filter_var(trim($form_state->getValue('miniorange_success_email_otp_message')));
    $error_message = filter_var(trim($form_state->getValue('miniorange_error_email_otp_message')));
    $blocked_message = filter_var(trim($form_state->getValue('miniorange_blocked_email_message')));
    $invalid_message = filter_var(trim($form_state->getValue('miniorange_invalid_otp_message')));

    $db_var->set('miniorange_success_email_otp_message', $success_message)
      ->set('miniorange_error_email_otp_message', $error_message)
      ->set('miniorange_blocked_email_message', $blocked_message)
      ->set('miniorange_invalid_otp_message', $invalid_message)
      ->save();

    \Drupal::messenger()->addMessage(t('Configurations saved successfully.'), 'status');

  }

  public function miniorange_phone_template_submit(array &$form, FormStateInterface $form_state)
  {

    $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');

    $success_message = filter_var(trim($form_state->getValue('miniorange_success_phone_otp_message')));
    $error_message = filter_var(trim($form_state->getValue('miniorange_error_phone_otp_message')));
    $blocked_message = filter_var(trim($form_state->getValue('miniorange_blocked_phone_message')));
    $invalid_message = filter_var(trim($form_state->getValue('miniorange_invalid_format_message')));

    $db_var->set('miniorange_success_phone_otp_message', $success_message)
      ->set('miniorange_error_phone_otp_message', $error_message)
      ->set('miniorange_blocked_phone_message', $blocked_message)
      ->set('miniorange_invalid_format_message', $invalid_message)
      ->save();

    \Drupal::messenger()->addMessage(t('Configurations saved successfully.'), 'status');

  }
}
