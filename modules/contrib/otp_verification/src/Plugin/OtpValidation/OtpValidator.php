<?php

namespace Drupal\otp_verification\Plugin\OtpValidation;

use Drupal\Core\Form\FormStateInterface;
use Drupal\otp_verification\Plugin\OtpValidationBase;
use Drupal\otp_verification\MiniorangeOtpUtilities;
use Drupal\otp_verification\MiniorangeOTPVerificationCustomer;
use Drupal\otp_verification\MiniorangeOTPVerificationConstants;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * @OtpValidation(
 *   id = "default_otp_validator",
 *   label = @Translation("Default OTP Validator")
 * )
 */
class OtpValidator extends OtpValidationBase implements ContainerFactoryPluginInterface {

  /**
   * Logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  protected $configFactory;

  /**
   * Constructs the plugin object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory,$config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('otp_verification');
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
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
  public function validate(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('otp_verification.settings');
    $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');

    MiniorangeOtpUtilities::isSessionStarted();
    $otp_options = $config->get('miniorange_otp_options');
    $mail = MiniorangeOtpUtilities::get_otp_cookie('arr2')['mail'];
    $otp_token = trim($form_state->getValue('otp') ?? '');
    $otp_token_phone = trim($form_state->getValue('phone_otp') ?? '');
    $trans_id = base64_decode(MiniorangeOtpUtilities::get_otp_cookie('trans_id'));
    $url = MiniorangeOTPVerificationConstants::BASE_URL . '/moas/api/auth/validate';
    $customer_key = $config->get('miniorange_otp_verification_customer_id');
    $api_key = $config->get('miniorange_otp_verification_customer_api_key');

    try {
      if ($otp_options === 'both') {
        $fields_email = [
          'token' => $otp_token,
          'txId' => $trans_id,
          'customerKey' => $customer_key,
          'username' => $mail,
          'authType' => 'EMAIL',
        ];

        $fields_phone = [
          'token' => $otp_token_phone,
          'txId' => $trans_id,
          'customerKey' => $customer_key,
          'username' => $mail,
          'authType' => 'PHONE',
        ];

        $customer = new MiniorangeOTPVerificationCustomer(NULL, NULL, NULL, NULL, $customer_key, $api_key);
        $response_email = json_decode($customer->callService($url, $fields_email, TRUE));
        $response_phone = json_decode($customer->callService($url, $fields_phone, TRUE));

        $db_var->set('miniorange_otp_token', $otp_token)->save();
        $db_var->set('miniorange_otp_token_phone', $otp_token_phone)->save();

        return ($response_email->status === 'SUCCESS' || $response_phone->status === 'SUCCESS');
      }

      $fields = [
        'token' => ($otp_options === 'phone') ? $otp_token_phone : $otp_token,
        'txId' => $trans_id,
        'customerKey' => $customer_key,
        'username' => $mail,
        'authType' => ($otp_options === 'phone') ? 'PHONE' : 'EMAIL',
      ];

      $customer = new MiniorangeOTPVerificationCustomer(NULL, NULL, NULL, NULL, $customer_key, $api_key);
      $response = json_decode($customer->callService($url, $fields, TRUE));

      $otp_key = ($otp_options === 'phone') ? 'miniorange_otp_token_phone' : 'miniorange_otp_token';
      $db_var->set($otp_key, $fields['token'])->save();

      return $response->status === 'SUCCESS';

    } catch (\Exception $e) {
      $this->logger->error('OTP validation failed: @message', ['@message' => $e->getMessage()]);
      return FALSE;
    }
  }

}
