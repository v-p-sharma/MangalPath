<?php

namespace Drupal\otp_verification\Form;

use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\otp_verification\MiniorangeOTPVerificationSupport;

class MiniorangeOTPVerificationRequestSupport extends FormBase
{

    public function getFormId()
    {
        return 'otp_verification_request_support';
    }

    /**
     * @return string[]
     */
    protected static function getDataDialogOptions()
    {
        return array('width' => '40%',);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getCustomerEmail()
    {
        $user      = \Drupal::currentUser();
        $config    = \Drupal::config('otp_verification.settings');
        $user_mail = $config->get('miniorange_otp_verification_customer_admin_email');
        return is_null($user_mail) ? $user->getEmail() : $user_mail;
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['mo_otp_verification_container'] = array(
            '#type'      => 'container',
            '#prefix'   => '<div id="modal_support_form">',
            '#suffix'   => '</div>',
        );

        $form['mo_otp_verification_container']['mo_otp_verification_script'] = array(
            '#attached' => array('library' => 'core/drupal.dialog.ajax'),
        );

        $form['mo_otp_verification_container']['mo_otp_verification_status_messages'] = array(
            '#type'     => 'status_messages',
            '#weight'   => -10,
        );

        $form['mo_otp_verification_container']['mo_otp_verification_description'] = array(
            '#markup'     => $this->t(
                '<p>Need any help? We can help you with configuring
                    <strong>OTP Verification Module</strong>
                    on your site. Just send us a query, and we will get back to you soon.</p>'
            ),
        );

        $form['mo_otp_verification_container']['mo_otp_verification_email_address'] = array(
            '#type'          => 'email',
            '#title'         => $this->t('Email'),
            '#default_value' => self::getCustomerEmail(),
            '#required'      => true,
            '#attributes'    => array(
                'placeholder'  => t('Enter your email'),
                'style'        => 'width:99%;margin-bottom:1%;'),
        );

        $form['mo_otp_verification_container']['mo_otp_verification_phone_number'] = array(
            '#type'          => 'textfield',
            '#title'         => $this->t('Phone'),
            '#attributes'    => array(
                'placeholder'  => $this->t('Enter number with country code Eg. +00xxxxxxxxxx'),
                'style'        => 'width:99%;margin-bottom:1%;',
                'pattern' => '^[+][0-9]{1,3}[0-9]{10}$',
                'title' => $this->t('Please enter a valid phone number with country code.'),
            ),
        );

        $form['mo_otp_verification_container']['mo_otp_verification_support_query'] = array(
            '#type'          => 'textarea',
            '#required'      => true,
            '#title'         => $this->t('Query'),
            '#attributes'    => array(
                'placeholder'  => t('Describe your query here!'),
                'style'        => 'width:99%',
                'maxlength' => 255,
                'minlength' => 10,
            ),
        );

        $form['mo_otp_verification_container']['actions'] = array(
            '#type' => 'actions',
        );

        $form['mo_otp_verification_container']['actions']['send'] = array(
            '#type'       => 'submit',
            '#value'      => $this->t('Submit'),
            '#attributes' => array(
                'class' => array(
                    'use-ajax',
                    'button--primary'
                ),
            ),
            '#ajax' => array(
                'callback' => '::submitModalFormAjax',
                'progress'  => array(
                    'type'    => 'throbber',
                    'message' => $this->t('Sending Query...'),
                ),
            ),
        );

        return $form;
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return AjaxResponse
     */
    public function submitModalFormAjax(array $form, FormStateInterface $form_state)
    {
        $form_values = $form_state->getValues();
        $response = new AjaxResponse();

        if ($form_state->hasAnyErrors()) {
            $response->addCommand(new ReplaceCommand('#modal_support_form', $form));
        } else {
            $email      = $form_values['mo_otp_verification_email_address'];
            $phone      = $form_values['mo_otp_verification_phone_number'];
            $query      = $form_values['mo_otp_verification_support_query'];

            $support          = new MiniorangeOTPVerificationSupport($email, $phone, $query);
            $support_response = $support->sendSupportQuery();

            if ($support_response) {
                $message = array(
                    '#type' => 'item',
                    '#markup' => $this->t('Thanks for getting in touch! We will get back to you shortly.'),
                );
                $ajax_form = new OpenModalDialogCommand('Thank you!', $message, static::getDataDialogOptions());
            } else {
                $error = array(
                    '#type' => 'item',
                    '#markup' => $this->t('Error submitting the support query. Please send us your query at
                             <a href="mailto:drupalsupport@xecurify.com">
                             drupalsupport@xecurify.com</a>.'),
                );
                $ajax_form = new OpenModalDialogCommand('Error!', $error, static::getDataDialogOptions());
            }
            $response->addCommand($ajax_form);
        }

        return $response;
    }


    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $phone = trim($form_state->getValue('mo_otp_verification_phone_number', ''));
        
        // Validate phone number if provided (field is optional but must be valid if filled)
        if (!empty($phone)) {
            // Remove whitespace for validation
            $clean_phone = preg_replace('/\s+/', '', $phone);
            
            // Validate format: + followed by 1-3 digits (country code) + 10 digits
            // Pattern matches: ^[+][0-9]{1,3}[0-9]{10}$
            if (!preg_match('/^\+[0-9]{1,3}[0-9]{10}$/', $clean_phone)) {
                $form_state->setErrorByName(
                    'mo_otp_verification_phone_number',
                    $this->t('Please enter a valid phone number with country code (e.g., +12345678901).')
                );
            }
        }
    }
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    }
}
