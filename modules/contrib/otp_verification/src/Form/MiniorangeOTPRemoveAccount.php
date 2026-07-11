<?php

namespace Drupal\otp_verification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\otp_verification\MoAuthUtilities;

class MiniorangeOTPRemoveAccount extends FormBase
{
    public function getFormId() {
        return 'otp_verification_remove_account';
    }

    public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
        $form['#prefix'] = '<div id="modal_example_form">';
        $form['#suffix'] = '</div>';
        $form['status_messages'] = [
            '#type' => 'status_messages',
            '#weight' => -10,
        ];

        $form['otp_verification_content'] = array(
            '#markup' => 'Are you sure you want to remove the account? The configurations saved will not be lost.'
        );

        $form['actions'] = array('#type' => 'actions');
        $form['actions']['send'] = [
            '#type' => 'submit',
            '#value' => $this->t('Confirm'),
            '#attributes' => [
                'class' => [
                    'use-ajax',
                ],
            ],
            '#ajax' => [
                'callback' => [$this, 'submitModalFormAjax'],
                'event' => 'click',
            ],
        ];

        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
        return $form;
    }

    public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
        $response = new AjaxResponse();
        $db_var = \Drupal::configFactory()->getEditable('otp_verification.settings');

      // If there are any form errors, AJAX replace the form.
        if ( $form_state->hasAnyErrors() ) {
            $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
        } else {
          $db_var->clear('miniorange_miniorange_otp_verification_customer_admin_email')
            ->clear('miniorange_otp_verification_customer_admin_phone')
            ->clear('miniorange_otp_verification_tx_id')
            ->clear('miniorange_otp_verification_customer_admin_password')
            ->clear('miniorange_otp_verification_status')
            ->clear('miniorange_otp_verification_customer_id')
            ->clear('miniorange_otp_verification_customer_api_key')
            ->clear('miniorange_otp_verification_customer_admin_token')
            ->save();


            \Drupal::messenger()->addStatus(t('Your Account Has Been Removed Successfully!'));
            $_POST['value_check'] = 'False';
            $response->addCommand(new RedirectCommand(\Drupal\Core\Url::fromRoute('otp_verification.customer_setup', ['tab'=> 'login'])->toString()));
        }
        return $response;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) { }

    public function submitForm(array &$form, FormStateInterface $form_state) { }

    protected function getEditableConfigNames() {
        return ['config.otp_verification_remove_account'];
    }
}
