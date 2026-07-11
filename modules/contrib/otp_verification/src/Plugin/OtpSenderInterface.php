<?php

namespace Drupal\otp_verification\Plugin;

interface OtpSenderInterface {
  /**
   * Sends an OTP using the provided contact details.
   *
   * @param string $email
   *   The email to send OTP.
   * @param string $phone
   *   The phone to send OTP.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function sendOtp(string $email, string $phone): bool;
}
