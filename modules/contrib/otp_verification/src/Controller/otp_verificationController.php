<?php
 /**
 * @file
 * Contains \Drupal\otp_verification\Controller\DefaultController.
 */

namespace Drupal\otp_verification\Controller;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\otp_verification\MiniorangeOtpUtilities;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\otp_verification\MiniorangeOtpVerificationConstants;
use Symfony\Component\HttpFoundation\Response;
use \Drupal\otp_verification\MiniorangeOTPVerificationCustomer;
use Drupal\Core\Form\formBuilder;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;



class otp_verificationController extends ControllerBase
{
  protected $formBuilder;
  public function __construct(FormBuilder $formBuilder) {
      $this->formBuilder = $formBuilder;
  }

  public static function create(ContainerInterface $container) {
      return new static(
          $container->get("form_builder")
      );
  }

  public function openModalForm() {
    $response = new AjaxResponse();
    $modal_form = $this->formBuilder->getForm('\Drupal\otp_verification\Form\MiniorangeOTPRemoveAccount');
    $response->addCommand(new OpenModalDialogCommand('Remove Account', $modal_form, ['width' => '800'] ) );
    return $response;
}

  function otp_user_logout()
  {
    global $base_url;

    $relayState = $base_url . "/user/login";
    \Drupal::service('session_manager')->destroy();
    $request = \Drupal::request();
    $request->getSession()->clear();

    if (!empty(\Drupal::config('otp_verification.settings')->get('otp_logout_url'))) {
      $logout_url = \Drupal::config('otp_verification.settings')->get('otp_logout_url');
      $response = new RedirectResponse($logout_url);
      $response->send();
    }
    $response = new RedirectResponse($relayState);
    $response->send();
    return new Response();
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

  }
}
