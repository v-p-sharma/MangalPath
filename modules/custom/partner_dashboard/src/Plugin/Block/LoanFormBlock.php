<?php
namespace Drupal\partner_dashboard\Plugin\Block;
use Drupal\Core\Block\BlockBase;
/**
* Provides a Custom block.
*
* @Block(
*   id = "loan_form_block",
*   admin_label = @Translation("Loan Apply Form Block"),
* )
*/
class LoanFormBlock extends BlockBase {
    /**
  * {@inheritdoc}
  */
  public function build() {
    $form =   $form = \Drupal::formBuilder()->getForm(
        'Drupal\partner_dashboard\Form\LoanApplicationForm'
        );
    return $form;
  }
 }