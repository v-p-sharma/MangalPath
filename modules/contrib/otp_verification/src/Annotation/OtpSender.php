<?php
namespace Drupal\otp_verification\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an OTP Sender plugin annotation.
 *
 * @Annotation
 */
class OtpSender extends Plugin {
  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin label.
   *
   * @var string
   */
  public $label;
}
