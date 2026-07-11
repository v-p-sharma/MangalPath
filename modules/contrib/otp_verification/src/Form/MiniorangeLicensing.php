<?php
/**
 * @file
 * Contains Licensing information for miniOrange OTP Verification Login Module.
 */

/**
 * Showing Licensing form info.
 */

namespace Drupal\otp_verification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\otp_verification\MiniorangeOtpUtilities;

class MiniorangeLicensing extends FormBase
{
  public function getFormId()
  {
    return 'miniorange_otp_verification_licensing';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    global $base_url;

    $form['markup_library'] = array(
      '#attached' => array(
        'library' => array(
          "otp_verification/otp_verification.admin",
          'core/drupal.dialog.ajax',
        )
      ),
    );

    $form['header_top_style_2'] = array(
      '#markup' => '<div class="mo_otp_verification_table_layout_1"><div class="mo_otp_verification_table_layout">'
    );

    $form['markup_1'] = array(
      '#markup' => '<br><h2>&emsp; Upgrade Plans</h2><hr>'
    );

    $admin_email = \Drupal::config('otp_verification.settings')->get('miniorange_otp_verification_customer_admin_email');

    $miniorange_gateway_upgrade_url = 'https://login.xecurify.com/moas/login?username='.$admin_email.'&redirectUrl=https://login.xecurify.com/moas/initializepayment&requestOrigin=drupal_otp_verification_basic_plan';

    $form['markup_free'] = array(
      '#markup' => '<html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <!-- Main Style -->
            </head>
            <body>
            <!-- Pricing Table Section -->
            <section id="mo_otp-pricing-table">
                <div class="mo_otp-container_1">
                    <div class="row">
                        <div class="pricing">
                            <div>
                                <div class="mo_otp-pricing-table mo_otp_class_inline_1">
                                    <div class="mo_otp-pricing-header" id="mo_otp-feature_list">
                                        <h2 class="pricing-title">Features / Plans</h2>
                                    </div>
                                    <div class="pricing-list">
                                        <ul>
                                            <li></li>
                                            <li>Email Address Verification</li>
                                            <li>Phone Number Verification</li>
                                            <li>Custom Email Template</li>
                                            <li>Custom SMS Template</li>
                                            <li>Default Country Code</li>
                                            <li>Send Custom SMS & Email Messages</li>
                                            <li>Custom OTP Length
                                            <li>Custom OTP Validity Time</li>
                                            <li>Custom Redirect URL after Register</li>
                                            <li>Custom Redirect URL after Logout</li>
                                            <li>Support various SMS gateways like msg91,twilio,etc.</li>
                                            <li>Support multiple international countries</li>
                                            <li>Support single international country</li>
                                            <li>One year plugin update</li>
                                            <li>Custom SMS/SMTP Gateway</li>
                                            <li>Custom Integration/Work</li>
                                            <li>Support</li>
                                        </ul>
                                    </div>
                                </div>
                            <div class="mo_otp-pricing-table mo_otp-class_inline">
                                <div class="mo_otp-pricing-header">
                                    <p class="pricing-title">FREE<span></span></span></p>
                                    <p class="pricing-rate">$0</sup></p>
                                    <h4 class="mo_otp-text_h4">10 SMS and 10 Email Verifications through miniOrange Gateway</h4>
                                    <div class="filler-class"></div>
                                     <a class="mbtn mo_otp_btn-custom mo_otp-mbtn-danger mo_otp-mbtn-sm">ACTIVE PLAN</a>
                                </div>
                                <div class="pricing-list">
                                    <ul>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li></li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li></li>
                                        <li></li>
                                        <li>Basic Email Support Available</li>
                                    </ul>
                                </div>
                            </div>


                        <div class="mo_otp-pricing-table mo_otp-class_inline">
                            <div class="mo_otp-pricing-header">
                                <p class="pricing-title">CUSTOM GATEWAY<br><span>[One Time Payment]</span></p>
                                <p class="pricing-rate">$99</p>

                                <h4 class="mo_otp-text_h4">Unlimited OTP Generation and Verification through the plugin</h4>
                                <h4 class="mo_otp-text_h4">SMS and Email delivery will be through your gateway</h4>
                                <div class="filler-class-custom-gateway"></div>
                                 <a href="https://www.miniorange.com/contact" target="_blank" class="mbtn mo_otp_btn-custom mo_otp-mbtn-danger mo_otp-mbtn-sm">CONTACT US</a>
                            </div>
                            <div class="pricing-list">
                                <ul>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li></li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li></li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li></li>
                                    <li>&#x2714;</li>
                                    <li>Basic Email Support Available</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mo_otp-pricing-table mo_otp-class_inline">
                            <div class="mo_otp-pricing-header">
                                <p class="pricing-title" id="mo_gateway">MINIORANGE GATEWAY <br></p>
                                <p class="pricing-rate">$0</p>
                                 <div class="filler-class-mo-gateway"></div>
                                 <a href="'. $miniorange_gateway_upgrade_url .'" target="_blank" class="mbtn mo_otp_btn-custom mo_otp-mbtn-danger mo_otp-mbtn-sm">UPGRADE NOW</a>
                            </div>

                            <div class="pricing-list">
                                <ul>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>Premium Email Support Available</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Pricing Table Section End -->
    </br>
   <div id="pricing_rate_note"><b>*</b>Transaction prices may vary depending on country. If you want to use more than 50k transactions, mail us at <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></div>
    </br>
    </body>
    </html>',

    );


$form['hello1'] = ['#type' => 'html_tag', '#tag' => 'script', '#attributes' => ["src" => "https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"]];

    $form['hello'] = ['#type' => 'html_tag', '#tag' => 'script', '#value' => $this->t('
            jQuery(document).ready(function($){
                $($("p.pricing-rate")[2]).append($(".js-form-item-mo-pricing-dropdown-sms"));
                $($("p.pricing-rate")[2]).append($(".js-form-item-mo-pricing-dropdown-email"));
            });
        '),];

    $form['mo_pricing_dropdown_sms'] = [
      '#type' => 'select',
      '#id' => 'mo_pricing_dropdown_sms',
      '#title' => t("<div class='mo_pricing_dropdown_sms'>&nbsp;&nbsp;SMS charges</div>"),
      '#options' => array(
        '1' => t('$2 per 100 OTP* + SMS Charges'),
        '2' => t('$5 per 500 OTP* + SMS Charges'),
        '3' => t('$7 per 1k OTP* + SMS Charges'),
        '4' => t('$20 per 5k OTP* + SMS Charges'),
        '5' => t('$30 per 10k OTP* + SMS Charges'),
        '6' => t('$45 per 50k OTP* + SMS Charges'),
      ),
    ];

    $form['mo_pricing_dropdown_email'] = [
      '#type' => 'select',
      '#id' => 'mo_pricing_dropdown_email',
      '#title' => t("<div class='mo_pricing_dropdown_email'>&nbsp;&nbsp;Email charges</div>"),
      '#options' => array(
        '1' => t('$2 per 100 Email'),
        '2' => t('$5 per 500 Email'),
        '3' => t('$7 per 1k Email'),
        '4' => t('$20 per 5k Email'),
        '5' => t('$30 per 10k Email'),
        '6' => t('$45 per 50k Email'),
      ),
    ];

    $this->showAddonsContent($form, $form_state);

    $form['main_layout_div_end_1'] = array(
      '#markup' => '</div>',
    );
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

  }


  function showAddonsContent(&$form, $form_state)
  {

    define("MO_ADDONS_CONTENT", serialize(array(

      "DRUPAL_SMS_NOTIFICATION" => [
        'addonName' => 'Drupal SMS Notification to Admin & User on Registration',
        'addonDescription' => 'Allows your site to send out custom SMS notifications to Customers and Administrators when a new user registers on your Drupal site. Click on the button above for further details.',
        'addonDescription' => 'Allows your site to send out custom SMS notifications to Customers and Administrators when a new user registers on your Drupal site. Click on the button above for further details.',
      ],
      "DRUPAL_PASSWORD_RESET" => [
        'addonName' => 'Drupal Password Reset Over OTP',
        'addonDescription' => 'Allows your users to reset their password using OTP instead of email links. Click on the button above for further details.',
      ],
      "REGISTER_USING_ONLY_PHONE" => [
        'addonName' => 'Register Using Only Phone Number',
        'addonDescription' => 'Allows your users to register on your Drupal site using only their Phone Number instead of email address. Click on the button above for further details.',
      ],
      "RESEND_OTP_CONTROL" => [
        'addonName' => 'Resend OTP Control',
        'addonDescription' => 'Allows you to block OTP from being sent out before the set timer is up. Click on the button above for further details.',
      ],
      "REGISTER_USING_OTP" => [
        'addonName' => 'Register Using OTP instead of Password ',
        'addonDescription' => 'Allows user to register using OTP instead of using Password. Click on the button above for further details.',
      ],
      "OTP_OVER_VOICE" => [
        'addonName' => 'OTP Over Voice',
        'addonDescription' => 'User will get the OTP over Voice or Phone call. Click on the button above for further details.',
      ],
      "OTP_OVER_WHATSAPP" => [
        'addonName' => 'OTP Over Whatsapp',
        'addonDescription' => 'User will get the OTP over WhatsApp. Click on the button above for further details.',
      ],
    )));



    $form['mo_otp_adddons'] = array(
      '#markup' => '<div class="mo_otp_wrapper">',
    );

    $messages = unserialize(MO_ADDONS_CONTENT);
    $icnt = 0;
    foreach ($messages as $messageKey) {
      $form['mo_otp_addonlist'.$icnt] = array(
        '#markup' => '<div id="' . $messageKey["addonName"] . '">
                           <h3 class="mo_text_align_center">' . $messageKey["addonName"] . '<br /><br /></h3>
                            <p class="mo_text_align_center">
                            <a href="https://www.miniorange.com/contact" class="mbtn mo_otp-mbtn-danger mbtn-large" id="mo_interesetd_addon" target="_blank">Interested</a>

                            </p>
                            <span class="cd-pricing-body">
                                <p class="addon_description">' . $messageKey["addonDescription"] . '</p>
                            </span>
                      </div>',

      );

      $icnt++;
    }

    $form['mo_otp_addons_lists'] = array(
      '#markup' => '</div>',
    );

    $form['asdf'] = array(
      '#markup' => '<div class="mo_otp_margin_btm"></div>'
    );
  }

}
