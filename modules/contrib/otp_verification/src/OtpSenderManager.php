<?php
namespace Drupal\otp_verification;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class OtpSenderManager extends DefaultPluginManager {
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/OtpSender',
      $namespaces,
      $module_handler,
      'Drupal\otp_verification\Plugin\OtpSenderInterface',
      'Drupal\otp_verification\Annotation\OtpSender'
    );
    $this->alterInfo('otp_sender_info');
    $this->setCacheBackend($cache_backend, 'otp_sender_plugins');
  }
}
