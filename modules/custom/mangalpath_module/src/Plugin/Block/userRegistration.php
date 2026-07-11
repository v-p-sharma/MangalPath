<?php

namespace Drupal\mangalpath_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "custom_user_registration_form",
 *   admin_label = @Translation("Custom User Registration Form")
 * )
 */
Class UserRegistration extends BlockBase implements ContainerFactoryPluginInterface {

  protected FormBuilderInterface $formBuilder;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  public function build() {
    // Render Drupal's built-in user registration form.
    // If your site uses a different namespace/class for RegisterForm,
    // adjust the class below accordingly.
    $user = \Drupal\user\Entity\User::create();
  return \Drupal::service('entity.form_builder')->getForm($user, 'register');

  }


}