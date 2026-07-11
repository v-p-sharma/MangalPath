<?php
namespace Drupal\partner_dashboard\Controller;
use Drupal\Core\Controller\ControllerBase;


class Property_selling_controller extends ControllerBase {
    public function PropertyForm() {
         $form = \Drupal::formBuilder()->getForm(
        'Drupal\partner_dashboard\Form\Property_selling'
        );
        return [
            
            '#theme' => 'property_listing_form',
            '#title'=> "test",
            '#form' => $form,
        ];
    }
}