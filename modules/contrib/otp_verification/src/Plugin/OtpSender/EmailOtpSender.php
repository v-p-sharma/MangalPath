<?php
namespace Drupal\otp_verification\Plugin\OtpSender;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\otp_verification\MiniorangeOTPVerificationConstants;
use Drupal\otp_verification\MiniorangeOTPVerificationCustomer;
use Drupal\otp_verification\Plugin\OtpSenderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * @OtpSender(
 *   id = "email_otp_sender",
 *   label = @Translation("Email OTP Sender")
 * )
 */
class EmailOtpSender implements OtpSenderInterface, ContainerFactoryPluginInterface {

  protected $logger;
  protected $configFactory;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory) {
    $this->logger = $logger_factory->get('otp_verification');
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function sendOtp($email,$phone): bool {

    try {
      list($status, $tx_id) = self::sendToken($email, $phone);

      if ($status === 'SUCCESS') {
        $this->logger->notice("OTP sent. TX ID: $tx_id");
        return true;
      }
      else {
        $this->logger->error("Failed to send OTP.");
        return false;
      }
    } catch (\Exception $e) {
      $this->logger->error("Exception while sending OTP: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Sends OTP using MiniOrange gateway.
   */
  public function sendToken($email, $phone) {
    $config = $this->configFactory->get('otp_verification.settings');
    $otp_options = $config->get('miniorange_otp_options');
    $customer_key = $config->get('miniorange_otp_verification_customer_id');
    $api_key = $config->get('miniorange_otp_verification_customer_api_key');
    $url = MiniorangeOTPVerificationConstants::BASE_URL . '/moas/api/auth/challenge';

    $mo_customer = new MiniorangeOTPVerificationCustomer(null, null, null, null, $customer_key, $api_key);
    $responses = [];

    if ($otp_options == 'both') {
      $fields_email = [
        'customerKey' => $customer_key,
        'email' => $email,
        'authType' => 'EMAIL',
        'transactionName' => 'Drupal OTP Verification',
      ];

      $fields_phone = [
        'customerKey' => $customer_key,
        'phone' => $phone,
        'authType' => 'SMS',
        'transactionName' => 'Drupal OTP Verification',
      ];

      $content_email = $mo_customer->callService($url, $fields_email, true);
      $content_phone = $mo_customer->callService($url, $fields_phone, true);

      $responses[] = json_decode($content_email, true);
      $responses[] = json_decode($content_phone, true);

    } else {
      $fields = [
        'customerKey' => $customer_key,
        'transactionName' => 'Drupal OTP Verification',
      ];
      if ($otp_options == 'email') {
        $fields['email'] = $email;
        $fields['authType'] = 'EMAIL';
      } elseif ($otp_options == 'phone') {
        $fields['phone'] = $phone;
        $fields['authType'] = 'SMS';
      }

      $content = $mo_customer->callService($url, $fields, true);
      $responses[] = json_decode($content, true);
    }

    foreach ($responses as $response) {
      if (isset($response['status']) && $response['status'] !== 'SUCCESS') {
        $this->logger->error(json_encode($response));
        return ['ERROR', null];
      }
    }

    $tx_id = isset($responses[0]['txId']) ? $responses[0]['txId'] : null;

    return ['SUCCESS', $tx_id];
  }
  /**
   * Sends OTP and returns status and transaction ID.
   *
   * @param string $email
   * @param string $phone
   * @return array
   *   An array like ['SUCCESS', 'txId'] or ['ERROR', null].
   */
  public function sendOtpWithResponse($email,$phone): array {
    try {
      return $this->sendToken($email, $phone);
    } catch (\Exception $e) {
      $this->logger->error("Exception while sending OTP: " . $e->getMessage());
      return ['ERROR', null];
    }
  }

}
