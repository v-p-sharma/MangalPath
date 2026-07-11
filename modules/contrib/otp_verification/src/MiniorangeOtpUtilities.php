<?php
/**
 * @file
 * This file is part of miniOrange OTP Verification plugin.
 */

namespace Drupal\otp_verification;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\otp_verification\Plugin\OtpSenderInterface;
use Drupal\user\Entity\User;

/**
 * OTP Utilities Class.
 */
class MiniorangeOtpUtilities {

  /**
   * Get Support query data from support form
   */
  public static function send_support_query(&$form, $form_state)
  {
    $email = trim($form['miniorange_otp_verification_email_address_support']['#value']);
    $phone = trim($form['miniorange_otp_verification_phone_number_support']['#value']);
    $query = trim($form['miniorange_otp_verification_support_query_support']['#value']);

    self::send_query($email, $phone, $query);
  }


  public static function send_query($email, $phone, $query)
  {
    if (empty($email) || empty($query)) {
      \Drupal::messenger()->addMessage(t('The <b><u>Email</u></b> and <b><u>Query</u></b> fields are mandatory.'), 'error');
      return;
    } elseif (!\Drupal::service('email.validator')->isValid($email)) {
      \Drupal::messenger()->addMessage(t('The email address <b><i>' . $email . '</i></b> is not valid.'), 'error');
      return;
    }
    $support = new MiniorangeOTPVerificationSupport($email, $phone, $query);
    $support_response = $support->sendSupportQuery();
    if ($support_response) {
      \Drupal::messenger()->addMessage(t('Thanks for getting in touch! We will get back to you soon.'), 'status');
    } else {
      \Drupal::messenger()->addMessage(t('An error occured while sending support query.'), 'error');
    }
  }

  /**
   * Advertise 2FA
   */

  public static function Two_FA_Advertisement(array &$form, FormStateInterface $form_state)
  {
    global $base_url;
    $form['markup_idp_attr_hea555der_top_support'] = array(
      '#markup' => '<div class="mo_otp_verification_table_layout_support_1 mo_otp_verification_container">',
    );

    $form['miniorangerr_otp_email_address'] = array(
      '#markup' => '<div><h4>Looking for a Drupal Two-Factor Authentication (2FA/MFA)?</h4></div>
                    <p>Two Factor Authentication (2FA) module adds a second layer of authentication at the time of login to secure your Drupal accounts. It is a highly secure and easy to setup module which protects your site from hacks and unauthorized login attempts.</p>',
    );

      $form['overview_download_module_button'] = [
          '#type' => 'link',
          '#title' => t('Download Module'),
          '#url' => Url::fromUri('https://www.drupal.org/project/miniorange_2fa'),
          '#attributes' => ['class' => ['button', 'button--primary'], 'target' => '_blank'],
      ];
      $form['overview_know_more_button'] = [
          '#type' => 'link',
          '#title' => t('Know More'),
          '#url' => Url::fromUri('https://plugins.miniorange.com/drupal-two-factor-authentication-2fa'),
          '#attributes' => ['class' => ['button'], 'target' => '_blank'],
      ];
  }

  /**
   * Check if curl is installed.
   */
  public static function isCurlInstalled()
  {
    if (in_array('curl', get_loaded_extensions())) {
      return 1;
    } else {
      return 0;
    }
  }

  /**
   * Check if customer is registered.
   */
  public static function isCustomerRegistered()
  {

    $config = \Drupal::config('otp_verification.settings');

    if ($config->get('miniorange_otp_verification_customer_admin_email') == NULL ||
      $config->get('miniorange_otp_verification_customer_id') == NULL ||
      $config->get('miniorange_otp_verification_customer_admin_token') == NULL ||
      $config->get('miniorange_otp_verification_customer_api_key') == NULL) {
      return FALSE;
    }
    return TRUE;
  }

  public static function sendToken($email, $phone)
  {
    $plugin_manager = \Drupal::service('plugin.manager.otp_sender');
    $plugin = $plugin_manager->createInstance('email_otp_sender');

    if ($plugin instanceof OtpSenderInterface) {
      return $plugin->sendOtpWithResponse($email, $phone);
    }
    return ['ERROR', null];

  }

  public static function Is_Restricted_Domain($email_domain)
  {

    $config = \Drupal::config('otp_verification.settings');

    $enable_domain_restriction = $config->get('miniorange_block_domain_value');

    if ($enable_domain_restriction === FALSE)
      return FALSE;

    $domain = isset(explode('@', $email_domain)[1]) ? explode('@', $email_domain)[1] : '';

    if (is_null($domain) || empty($domain))
      return FALSE;

    $blockdomains = $config->get('miniorange_block_domains');
    $blockdomains = explode(';', $blockdomains);
    $white_or_black = $config->get('miniorange_domains_are_white_or_black');

    if ($white_or_black === 'white') {
      if (array_search($domain, $blockdomains) === FALSE) {
        return TRUE;
      } else return FALSE;
    } elseif ($white_or_black == 'black') {
      if (array_search($domain, $blockdomains) === FALSE) {
        return FALSE;
      } else return TRUE;
    }

  }

  public static function Is_Restricted_Country_Code($phone)
  {

    $config = \Drupal::config('otp_verification.settings');

    $enable_cc_restriction = $config->get('miniorange_enable_country_code_restriction');

    if ($enable_cc_restriction === 0)
      return FALSE;

    $country_codes = $config->get('miniorange_country_codes');
    $country_codes = explode(';', $country_codes);
    $allow_or_block = $config->get('miniorange_allow_or_block_country_code');

    if ($allow_or_block === 'allow') {

      foreach ($country_codes as $code) {
        if (strpos($phone, $code) !== false)
          return FALSE;
      }

      return TRUE;
    } elseif ($allow_or_block == 'block') {

      foreach ($country_codes as $code) {
        if (strpos($phone, $code) !== false)
          return TRUE;
      }

      return FALSE;
    }

  }

  public static function load_user_by_name($name)
  {
    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties([
        'name' => $name,
      ]);

    return $users ? reset($users) : FALSE;
  }

  public static function drupal_is_cli()
  {
    $server = \Drupal::request()->server;
    $server_software = $server->get('SERVER_SOFTWARE');
    $server_argc = $server->get('argc');
    return !isset($server_software) && (php_sapi_name() == 'cli' || (is_numeric($server_argc) && $server_argc > 0));
  }

  public static function mo_get_drupal_core_version() {
    return \DRUPAL::VERSION;
  }

  public static function isSessionStarted(){
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  public static function getNestedValue($arr){
    if( !is_array($arr) ){
      return $arr;
    }
    foreach ($arr as $key => $value){
      return self::getNestedValue($value);
    }
  }

  public static function set_otp_cookie($name, $value){
    setrawcookie('Drupal.visitor.' . $name, base64_encode(json_encode($value)), \Drupal::time()->getRequestTime() + 3900, '/');
  }

  public static function get_otp_cookie($name){
    return json_decode(base64_decode($_COOKIE['Drupal_visitor_'.$name]),true);
  }

  public static function customUserFields()
  {
    $custom_fields = array('select' => '- Select Field Name -');
    $usr = User::load(\Drupal::currentUser()->id());
    $usrVal = $usr->toArray();
    foreach ($usrVal as $key => $value) {
      if (strpos($key, 'field_') === 0) {
        $label = $key;
        try {
          $field = FieldConfig::loadByName('user', 'user', $key);
          $label = $field->label();
        } catch (\Exception $e) {
          \Drupal::logger('otp_verification')->error($e);
        }
        $custom_fields[$key] = $label;
      }
    }
    return $custom_fields;
  }

  public static function getOtpTemplateUrl($path, $is_registered, $mo_server_url, $base_url) {
    return Url::fromUri($is_registered ? $mo_server_url . $path : $base_url . '/admin/config/people/otp_verification/customer_setup');
  }

}
