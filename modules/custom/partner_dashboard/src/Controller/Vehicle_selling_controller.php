<?php
namespace Drupal\partner_dashboard\Controller;
use Drupal\Core\Controller\ControllerBase;


class Vehicle_selling_controller extends ControllerBase {
    public function VehicleForm() {
         $form = \Drupal::formBuilder()->getForm(
        'Drupal\partner_dashboard\Form\Vehicle_selling_rental'
        );
        return [
            
            '#theme' => 'vehicle_listing_form',
            '#title'=> "test",
            '#form' => $form,
        ];
    }
}