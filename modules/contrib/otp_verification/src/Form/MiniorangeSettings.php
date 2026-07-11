<?php

/**
 * @file
 * Contains \Drupal\otp_verification\Form\MiniorangeSettings.
 */

namespace Drupal\otp_verification\Form;

use Drupal\Core\Url;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\otp_verification\MiniorangeOTPVerificationConstants;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\otp_verification\MiniorangeOtpUtilities;

class MiniorangeSettings extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['otp_verification.settings'];
  }
  public function getFormId()
  {
    return 'miniorange_otp_verification_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    global $base_url;
    $config = $this->config('otp_verification.settings');
    $conf_url = $base_url . '/admin/config/people/otp_verification/configuration';
    $config_url = $base_url . '/admin/config/people/accounts';
    $isCustomerRegisterd = !MiniorangeOtpUtilities::isCustomerRegistered();

    $form['markup_library'] = array(
      '#attached' => array(
        'library' => array(
          "otp_verification/otp_verification.admin",
          'core/drupal.dialog.ajax',
        )
      ),
    );

    $form['header_top_style_2'] = array('#markup' => '<div class="mo_otp_verification_table_layout_1" ><div class="mo_otp_verification_table_layout mo_settings_container">');

    $form['miniorange_otp_customer_validation'] = array(
      '#method' => 'post',
      '#type' => 'hidden',
      '#id' => 'mo_otp_verification_settings',
      '#value' => 'mo_customer_validation_settings',
    );

    $form['markup_1'] = [
      '#type' => 'item',
      '#title' => t('<span style="font-size: 25px">OTP Verification Settings</span>'),
      '#suffix' => '<hr>'
    ];

    if ($isCustomerRegisterd) {
      $register_url = $base_url . '/admin/config/people/otp_verification/customer_setup';
      $form['header'] = array(
        '#markup' => '<div class="mo_saml_configure_message">You need to <a href="' . $register_url . '" >register/login</a> with miniOrange before using this module.</div><br>',
      );
    }
    $form['miniorange_otp_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Verification Method'),
      //'#default_value' => $config->get('miniorange_otp_options') ?? 'email',
      '#options' => [
        'email' => $this->t('Email Verification'),
        'phone' => $this->t('Phone Verification'),
        'both'  => $this->t('Both Email & Phone Verification'),
      ],
      '#config_target' => 'otp_verification.settings:miniorange_otp_options',
      '#disabled' => $isCustomerRegisterd,
      '#attributes' => ['class' => ['inline-radios']],
    ];

    $accountConfigUrl = Url::fromRoute('entity.user.field_ui_fields')->toString();
    $custom_fields = MiniorangeOtpUtilities::customUserFields();

    $form['mo_phone_field_name'] = array(
      '#type' => 'select',
      '#title' => $this->t('Phone number field name'),
      '#options' => $custom_fields,
      '#default_value' => $config->get('machine_name_of_phone_field'),
      '#states' => array(
        'visible' => array(
          array(':input[name="miniorange_otp_options"]' => array('value' => 'phone')),
          'or',
          array(':input[name="miniorange_otp_options"]' => array('value' => 'both')),
        ),
      ),
      '#description' => t('<a target="_blank" href="' . $accountConfigUrl . '">Click here</a> to check available fields on your Drupal site.'),
    );

    $form['Country_Code_field']['country_code_restriction_checkbox'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Check this option if you want  <b>Country Code Restriction</b>'),
      '#default_value' => $config->get('miniorange_enable_country_code_restriction'),
      '#disabled' => $isCustomerRegisterd,
      '#states' => array(
        // Only show this field when the checkbox is enabled.
        'visible' => array(
          ':input[name="miniorange_otp_options"]' => array('value' => 'phone'),
        ),
      ),
    );

    $form['Country_Code_field']['miniorange_set_of_radiobuttons_country'] = array(
      '#type' => 'fieldset',
      '#states' => array(
        // Only show this field when the checkbox is enabled.
        'visible' => array(
          ':input[name="country_code_restriction_checkbox"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['Country_Code_field']['miniorange_set_of_radiobuttons_country']['miniorange_allow_or_block_country_code'] = array(
      '#type' => 'radios',
      '#maxlength' => 5,
      '#options' => array('allow' => 'I want to allow only some of the country codes', 'block' => 'I want to block some of the country codes'),
      '#default_value' => is_null($config->get('miniorange_allow_or_block_country_code')) ? 'allow' : $config->get('miniorange_allow_or_block_country_code'),
      '#disabled' => $isCustomerRegisterd,
    );


    $form['Country_Code_field']['miniorange_set_of_radiobuttons_country']['miniorange_country_codes'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Enter list of country codes'),
      '#tree' => TRUE,
      '#attributes' => array(
        'style' => 'width:700px;height:70px;',
        'placeholder' => t('Eg. +xx;+xxx;'),
      ),
      '#description' => t('Enter semicolon(;) separated country codes with (+) sign (Eg. +xx; +xxx)'),
      '#default_value' => is_null($config->get('miniorange_country_codes')) ? '' : $config->get('miniorange_country_codes'),
      '#suffix' => '<br>',
    );

    $form['mo_field_set_domain_restriction'] = [
      '#type' => 'details',
      '#title' => $this->t('Domain Restriction'),
      '#open' => TRUE
    ];

    $form['mo_field_set_domain_restriction']['domain_restriction_checkbox'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Check this option if you want  <b>Domain Restriction</b>'),
      '#default_value' => $config->get('miniorange_block_domain_value'),
      '#disabled' => $isCustomerRegisterd,
    );

    $form['mo_field_set_domain_restriction']['miniorange_set_of_radiobuttons'] = array(
      '#type' => 'fieldset',
      '#states' => array(
        // Only show this field when the checkbox is enabled.
        'visible' => array(
          ':input[name="domain_restriction_checkbox"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['mo_field_set_domain_restriction']['miniorange_set_of_radiobuttons']['miniorange_allow_or_block_domains'] = array(
      '#type' => 'radios',
      '#maxlength' => 5,
      '#options' => array('white' => 'I want to allow only some of the domains', 'black' => 'I want to block some of the domains'),
      '#default_value' => is_null($config->get('miniorange_domains_are_white_or_black')) ? 'white' : $config->get('miniorange_domains_are_white_or_black'),
      '#disabled' => $isCustomerRegisterd,
    );


    $form['mo_field_set_domain_restriction']['miniorange_set_of_radiobuttons']['miniorange_domains'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Enter list of domains'),
      '#tree' => TRUE,
      '#attributes' => array(
        'style' => 'width:700px;height:70px;',
        'placeholder' => t('Eg. xxxx.com;xxxx.com;'),
      ),
      '#description' => t('Enter semicolon(;) separated domains (Eg. xxxx.com; xxxx.com)'),
      '#default_value' => is_null($config->get('miniorange_block_domains')) ? '' : $config->get('miniorange_block_domains'),
    );
    $form['mo_fieldset_redirect_url'] = [
      '#type' => 'details',
      '#title' => $this->t('Redirect URL '),
      '#open' => TRUE
    ];

    $form['mo_fieldset_redirect_url']['otp_login_url'] = array(
      '#type' => 'textfield',
      '#disabled' => $isCustomerRegisterd,
      '#title' => $this->t('Default Redirect URL after Registration'),
      '#default_value' =>\Drupal::config('otp_verification.settings')->get('miniorange_redirect_after_login'),
      '#attributes' => array('style' => 'width:73%','placeholder' => 'Enter Default Redirect URL'),
    );

    $form['mo_fieldset_customize'] = [
      '#type' => 'details',
      '#title' => $this->t('Sms/Email Configurations '),
      '#open' => TRUE
    ];

    $admin_email = $config->get('miniorange_otp_verification_customer_admin_email');
    $mo_server_url = MiniorangeOtpVerificationConstants::BASE_URL . '/moas/login?username=' . $admin_email . '&redirectUrl=' . MiniorangeOtpVerificationConstants::BASE_URL . '/moas/admin/customer';

    $is_registered = MiniorangeOtpUtilities::isCustomerRegistered();

    $otp_length_config = MiniorangeOtpVerificationConstants::BASE_URL . '/moas/login?username=' . $admin_email . '&redirectUrl=' . MiniorangeOtpVerificationConstants::BASE_URL . '/moas/admin/customer/customerpreferences';

    $form['mo_fieldset_customize']['custom_sms_template'] = [
      '#type' => 'link',
      '#title' => $this->t('Custom SMS Template'),
      '#url' => MiniorangeOtpUtilities::getOtpTemplateUrl('/showsmstemplate', $is_registered, $mo_server_url, $base_url),
      '#attributes' => ['target' => '_blank'],
      '#prefix' => '<b>Look at the sections below to customize the Email and SMS that you receive:</b><br>',
      '#suffix' => '<br>',
    ];

    $form['mo_fieldset_customize']['custom_email_template'] = [
      '#type' => 'link',
      '#title' => $this->t('Custom Email Template'),
      '#url' => MiniorangeOtpUtilities::getOtpTemplateUrl('/showemailtemplate', $is_registered, $mo_server_url, $base_url),
      '#attributes' => ['target' => ''],
      '#suffix' => '<br>',
    ];

    $form['mo_fieldset_customize']['otp_length_preference'] = [
      '#type' => 'link',
      '#title' => $this->t('Custom OTP Length'),
      '#url' => Url::fromUri($otp_length_config),
      '#attributes' => ['target' => ''],
      '#prefix' => '<br></br><b>Look at the sections below to customize the OTP that you receive:</b><br>',
    ];

    $form['mo_fieldset_customize']['otp_validity_preference'] = [
      '#type' => 'link',
      '#title' => $this->t('Custom OTP Validity'),
      '#url' => Url::fromUri($otp_length_config),
      '#attributes' => ['target' => ''],
      '#prefix' => '<br>',
    ];


    $form['tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Templates Sections'),
    ];
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('Email Messages'),
      '#group' => 'tabs',
      '#open' => TRUE, // Open by default.
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS/Mobile Messages'),
      '#group' => 'tabs',
    ];

    $form['common'] = [
      '#type' => 'details',
      '#title' => $this->t('Common Messages'),
      '#group' => 'tabs',
    ];

    $form['general']['miniorange_success_email_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Success OTP Message'),
      '#default_value' => !empty($config->get('miniorange_success_email_otp_message', '')) ? $config->get('miniorange_success_email_otp_message') : t('A One Time Passcode has been sent to ##email##. Please enter the OTP below to verify your email address. If you cannot see the email in your inbox, make sure to check your SPAM folder.'),
      '#description' => t('<b>Note: </b>##email## in the message body will be replaced by the user email address.'),
    );

    $form['general']['miniorange_error_email_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Error OTP Message'),
      '#default_value' => !empty($config->get('miniorange_error_email_otp_message', '')) ? $config->get('miniorange_error_email_otp_message') : t('There was an error in sending the OTP.'),
    );

    $form['general']['miniorange_blocked_email_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Blocked EMAIL Message'),
      '#default_value' => !empty($config->get('miniorange_blocked_email_message', '')) ? $config->get('miniorange_blocked_email_message') : t('You are not allowed to register. Please contact your site administrator. Your domain may be blocked by admin.'),

    );

    $form['common']['miniorange_invalid_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Invalid OTP Message'),
      '#default_value' => !empty($config->get('miniorange_invalid_otp_message', '')) ? $config->get('miniorange_invalid_otp_message') : t('OTP entered is incorrect. Please enter valid OTP.'),
      '#suffix' => '<br>',
    );

    $form['advanced']['miniorange_success_phone_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Success OTP Message'),
      '#default_value' => !empty($config->get('miniorange_success_phone_otp_message', '')) ? $config->get('miniorange_success_phone_otp_message') : t('A One Time Passcode has been sent to ##phone##. Please enter the OTP below to verify your phone number.'),
      '#description' => t('<b>Note: </b>##phone## in the message body will be replaced by the user phone number.'),
    );

    $form['advanced']['miniorange_error_phone_otp_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Error OTP Message'),
      '#default_value' => !empty($config->get('miniorange_error_phone_otp_message', '')) ? $config->get('miniorange_error_phone_otp_message') : t('There was an error in sending the OTP.'),
    );

    $form['advanced']['miniorange_blocked_phone_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Blocked Country Code Message'),
      '#default_value' => !empty($config->get('miniorange_blocked_phone_message', '')) ? $config->get('miniorange_blocked_phone_message') : t('You are not allowed to register. Please contact your site administrator. Your country code may be blocked by admin.'),
    );

    $form['advanced']['miniorange_invalid_format_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Invalid Phone Number Format Message'),
      '#default_value' => !empty($config->get('miniorange_invalid_format_message', '')) ? $config->get('miniorange_invalid_format_message') : t('Enter a valid phone number.'),
      '#suffix' => '<br>',
    );

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Configurations'),
      '#disabled' => $isCustomerRegisterd,
    ];

    return parent::buildForm($form, $form_state);
  }


  public function validateForm(array &$form, FormStateInterface $form_state){
    $form_values = $form_state->getValues();
    if($form_values['domain_restriction_checkbox'] && empty(trim($form_values['miniorange_domains']))){
      $form_state->setErrorByName("miniorange_domains",$this->t("<i>Domain field</i> is required."));
    }
  }

  /**
   * Handling Save Settings tab.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    global $base_url;
    $config = $this->configFactory()->getEditable('otp_verification.settings');

    $user = User::load(\Drupal::currentUser()->id());

    $domains = null;

    $block_email_domains = $form_state->getValue(['domain_restriction_checkbox']);

    if ($block_email_domains == 1) {
      $block_email_domains = TRUE;
      $domains = trim($form['mo_field_set_domain_restriction']['miniorange_set_of_radiobuttons']['miniorange_domains']['#value']);
      if (empty($domains)) {
        \Drupal::messenger()->addMessage(t('Domain field is required.'), 'error');
        return;
      }
    } else {
      $block_email_domains = FALSE;
    }
    $white_or_black = $form_state->getValue(['miniorange_allow_or_block_domains']);
    $success_message = filter_var(trim($form_state->getValue('miniorange_success_email_otp_message')));
    
    $error_message = filter_var(trim($form_state->getValue('miniorange_error_email_otp_message')));
    $blocked_message = filter_var(trim($form_state->getValue('miniorange_blocked_email_message')));
    $invalid_message = filter_var(trim($form_state->getValue('miniorange_invalid_otp_message')));
    $redirect_url = $form_state->getValue('otp_login_url');

    $user_enabled = $form_state->getValue('miniorange_otp_options');
    $phone_field = $form_state->getValue('mo_phone_field_name');

    if ($user_enabled == 'phone' && $phone_field == 'select') {
      $this->messenger()->addError($this->t('Machine Name of the Phone field is required.'));
      return;
    }

    $enable_country_code_restriction = $form_state->getValue('country_code_restriction_checkbox');
    $country_codes = trim($form_state->getValue('miniorange_country_codes'));
    $allow_or_block_country_code = $form_state->getValue('miniorange_allow_or_block_country_code');

    if ($enable_country_code_restriction == 1 && empty($country_codes)) {
      $this->messenger()->addError($this->t('Country Codes field is required.'));
      return;
    }

    // Save configuration
    $config->set('machine_name_of_phone_field', $phone_field)
      ->set('miniorange_otp_options', $user_enabled)
      //->set('miniorange_otp_options_phone', $user_enabled_phone)
      ->set('miniorange_block_domain_value', $block_email_domains)
      ->set('miniorange_block_domains', $domains)
      ->set('miniorange_redirect_after_login',$redirect_url)
      ->set('miniorange_domains_are_white_or_black', $white_or_black)
      ->set('miniorange_enable_country_code_restriction', $enable_country_code_restriction)
      ->set('miniorange_allow_or_block_country_code', $allow_or_block_country_code)
      ->set('miniorange_country_codes', $country_codes)
      ->set('miniorange_success_email_otp_message', $success_message)
      ->set('miniorange_error_email_otp_message', $error_message)
      ->set('miniorange_blocked_email_message', $blocked_message)
      ->set('miniorange_invalid_otp_message', $invalid_message)
      ->save();

    drupal_flush_all_caches();
    $logout_url = $base_url . '/user/logout';
    $message = $this->t('Settings saved successfully. You can go to your registration form page to test the plugin. <a href="@logout">Click here</a> to logout.', ['@logout' => $logout_url]);

    $this->messenger()->addStatus($message);
  }
}
