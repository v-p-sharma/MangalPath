<?php

namespace Drupal\otp_verification;

/**
 * @file
 * This class represents support information for customer.
 */

use \Drupal\otp_verification\MiniorangeOtpUtilities;
use \Drupal\otp_verification\MiniorangeOTPVerificationConstants;

/**
 * @file
 * Contains miniOrange Support class.
 */
class MiniorangeOTPVerificationSupport
{
  public $email;
  public $phone;
  public $query;

  public function __construct($email, $phone, $query)
  {
    $this->email = $email;
    $this->phone = $phone;
    $this->query = $query;
  }

    /**
     * Send support query.
     */
    public function sendSupportQuery()
    {
        if (!MiniorangeOtpUtilities::isCurlInstalled()) {
            return (object)(array (
                "status" => 'CURL_ERROR',
                "message" => 'PHP cURL extension is not installed or disabled.'
            ));
        }

        $modules_info    = \Drupal::service('extension.list.module')->getExtensionInfo('otp_verification');
        $modules_version = $modules_info['version'];
        $version         = $modules_version . ' | PHP '. phpversion();
        $drupal_version  = MiniorangeOtpUtilities::mo_get_drupal_core_version();

        $this->query = '[Drupal ' . $drupal_version . ' OTP Verification Module | ' . $version. '] ' . $this->query;

        $fields = array (
            'company' => $_SERVER['SERVER_NAME'],
            'email'   => $this->email,
            'phone'   => $this->phone,
            'ccEmail' => 'drupalsupport@xecurify.com',
            'query'   => $this->query
        );
        $field_string = json_encode($fields);

        $url = MiniorangeOTPVerificationConstants::BASE_URL . MiniorangeOTPVerificationConstants::SUPPORT_QUERY;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array (
            'Content-Type: application/json',
            'charset: UTF-8',
            'Authorization: Basic'
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        curl_exec($ch);
        if (curl_errno($ch)) {
            $error = "cURL Error at <strong>sendSupportQuery</strong> function of <strong>mo_auth_support.php</strong> file: ";
            \Drupal::logger('otp_verification')->error($error . curl_error($ch));
            return false;
        }

        return true;
    }
}
