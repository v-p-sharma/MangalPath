<?php

namespace Drupal\otp_verification;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Manages OTP Validation plugins.
 */
class OtpValidationPluginManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/OtpValidation',
      $namespaces,
      $module_handler,
      'Drupal\otp_verification\Plugin\OtpValidatorInterface',
      'Drupal\otp_verification\Annotation\OtpValidation'
    );

    $this->alterInfo('otp_validation_info');
    $this->setCacheBackend($cache_backend, 'otp_validation_plugins');
  }

}
