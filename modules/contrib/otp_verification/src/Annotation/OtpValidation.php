<?php

namespace Drupal\otp_verification\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an OTP Validation Plugin annotation object.
 *
 * @Annotation
 */
class OtpValidation extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label for the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
