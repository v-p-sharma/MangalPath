<?php
namespace Drupal\otp_verification\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\otp_verification\Plugin\OtpSenderInterface;

/**
 * Base class for OTP sender plugins.
 */
abstract class OtpSenderPluginBase extends PluginBase implements OtpSenderInterface {

}
