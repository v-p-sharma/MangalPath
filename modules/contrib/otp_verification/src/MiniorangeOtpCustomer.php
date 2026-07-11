<?php
/**
 * @file
 * Contains miniOrange Customer class.
 */

namespace Drupal\otp_verification;

use \Drupal\otp_verification\MiniorangeOTPVerificationConstants;
use \Drupal\otp_verification\MiniorangeOTPVerificationCustomer;
/**
 * @file
 * This class represents configuration for customer.
 */
class MiniorangeOtpCustomer
{

  public $email;
  public $phone;
  public $customerKey;
  public $transactionId;
  public $password;
  public $otpToken;
  public $defaultCustomerId;
  public $defaultCustomerApiKey;

  public static $pcode = "ZHJ1cGFsX290cF92ZXJpZmljYXRpb25fYmFzaWNfcGxhbg==";              //drupal_otp_verification_basic_plan
  public static $bcode = "ZHJ1cGFsX290cF92ZXJpZmljYXRpb25fcHJlbWl1bV9wbGFu";              //drupal_otp_verification_premium_plan

  /**
   * Constructor.
   */
  public function __construct($email, $phone, $password, $otp_token)
  {
    $this->email = $email;
    $this->phone = $phone;
    $this->password = $password;
    $this->otpToken = $otp_token;
    $this->defaultCustomerId = '16555';
    $this->defaultCustomerApiKey = 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
  }


  /**
   * Value empty or null.
   */
  public static function moCheckEmptyOrNull($value)
  {
    if (!isset($value) || empty($value)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Check customer license.
   */
  public function checkCustomerLn()
  {
    $config = \Drupal::config('otp_verification.settings');

    $url = MiniorangeOTPVerificationConstants::BASE_URL . '/moas/rest/customer/license';

    /* The customer Key provided to you */
    $customer_key = $config->get('miniorange_otp_verification_customer_id');
    $api_key = $config->get('miniorange_otp_verification_customer_api_key');

    // Check for otp over sms/email.
    $fields = array(
      'customerId' => $customer_key,
      'applicationName' => 'drupal_otp',
    );

    $mo_otp_verification_customer = new MiniorangeOTPVerificationCustomer($this->email, $this->phone,null,null,$customer_key,$api_key);
    return $mo_otp_verification_customer->callService($url,$fields,true);
  }
}
