<?php

namespace Drupal\otp_verification\Plugin;

/**
 * Interface for OTP Validator plugins.
 */
interface OtpValidatorInterface {

  /**
   * Validate the OTP for the user.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return bool
   *   TRUE if validation is successful, FALSE otherwise.
   */
  public function validate(array $form, \Drupal\Core\Form\FormStateInterface $form_state);

}
