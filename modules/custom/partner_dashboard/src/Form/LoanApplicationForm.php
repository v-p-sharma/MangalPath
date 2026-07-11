<?php

namespace Drupal\partner_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;

class LoanApplicationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'loan_application_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    //   $form['#attributes']['id'] = 'loanForm';
    // $form['#attributes']['class'][] = 'loan-form';
    // $form['#tree'] = TRUE;
    /*
    |--------------------------------------------------------------------------
    | Applicant Details
    |--------------------------------------------------------------------------
    */

    $form['applicant'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['row', 'g-3'],
      ],
      '#prefix' => '
      <h5 class="text-primary fw-bold mb-3 border-bottom pb-2">
        Applicant Details
      </h5>',
    ];
        $form['applicant']['field_first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'Enter first name',
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['applicant']['field_last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'Enter last name',
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['applicant']['field_father_s_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Father's Name"),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => "Enter father's name",
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['applicant']['field_mother_s_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Mother's Name"),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => "Enter mother's name",
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['applicant']['field_date_of_birth'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of Birth'),
      '#attributes' => [
        'class' => ['form-control'],
      ],
      '#prefix' => '<div class="col-md-4">',
      '#suffix' => '</div>',
    ];
        $form['applicant']['field_gender'] = [
      '#type' => 'select',
      '#title' => $this->t('Gender'),
      '#options' => [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
      ],
      '#attributes' => [
        'class' => ['form-control'],
      ],
      '#prefix' => '<div class="col-md-4">',
      '#suffix' => '</div>',
    ];
        $form['applicant']['field_caste_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Caste / Category'),
      '#options' => [
        'general' => 'General',
        'obc' => 'OBC',
        'sc' => 'SC',
        'st' => 'ST',
        'other' => 'Other',
      ],
      '#attributes' => [
        'class' => ['form-control'],
      ],
      '#prefix' => '<div class="col-md-4">',
      '#suffix' => '</div>',
    ];
        $form['applicant']['field_loan_email_address'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'your@email.com',
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['applicant']['field_phone_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => '+91 XXXXX XXXXX',
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        /*
    |--------------------------------------------------------------------------
    | Address Details
    |--------------------------------------------------------------------------
    */

    $form['address'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['row', 'g-3'],
      ],
      '#prefix' => '
      <h5 class="text-primary fw-bold mb-3 mt-4 border-bottom pb-2">
        Address Details
      </h5>',
    ];
        $form['address']['field_current_address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Current Address'),
      '#required' => TRUE,
      '#rows' => 2,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'Full current address',
      ],
      '#prefix' => '<div class="col-12">',
      '#suffix' => '</div>',
    ];
        $form['address']['field_permanent_address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Permanent Address'),
      '#required' => TRUE,
      '#rows' => 2,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'Permanent address',
      ],
      '#prefix' => '<div class="col-12">',
      '#suffix' => '</div>',
    ];
        /*
    |--------------------------------------------------------------------------
    | Employment & Financial Details
    |--------------------------------------------------------------------------
    */

    $form['employment'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['row', 'g-3'],
      ],
      '#prefix' => '
      <h5 class="text-primary fw-bold mb-3 mt-4 border-bottom pb-2">
        Employment & Financial Details
      </h5>',
    ];
        $form['employment']['field_employee_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Employee Type'),
      '#required' => TRUE,
      '#options' => [
        'select_type' => 'Select Type',
        'salaried' => 'Salaried',
        'self_employed' => 'Self-Employed',
        'business_owner' => 'Business Owner',
        'agriculturist' => 'Agriculturist',
        'student' => 'Student',
        'other' => 'Other',
      ],
      '#attributes' => [
        'class' => ['form-control'],
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['employment']['field_annual_income'] = [
      '#type' => 'number',
      '#title' => $this->t('Annual Income (₹)'),
      '#required' => TRUE,
      '#min' => 0,
      '#step' => 1,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'e.g. 500000',
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['employment']['field_current_running_loan'] = [
      '#type' => 'radios',
      '#title' => $this->t('Current Running Loan?'),
      '#default_value' => 'No',
      '#options' => [
        'No' => 'No',
        'Yes' => 'Yes',
      ],
      '#ajax' => [
        'callback' => '::runningLoanCallback',
        'wrapper' => 'running-loan-wrapper',
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['employment']['running_loan_wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div id="running-loan-wrapper" class="col-md-6">',
      '#suffix' => '</div>',
    ];
        if ($form_state->getValue(['employment', 'field_current_running_loan'], 'No') == 'Yes') {

      $form['employment']['running_loan_wrapper']['field_if_yes_loan_amount'] = [
        '#type' => 'number',
        '#title' => $this->t('If Yes, Loan Amount (₹)'),
        '#min' => 0,
        '#step' => 1,
        '#attributes' => [
          'class' => ['form-control'],
          'placeholder' => 'Enter outstanding amount',
        ],
      ];

    }
        /*
    |--------------------------------------------------------------------------
    | Loan Requirements
    |--------------------------------------------------------------------------
    */

    $form['loan'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['row', 'g-3'],
      ],
      '#prefix' => '
      <h5 class="text-primary fw-bold mb-3 mt-4 border-bottom pb-2">
        Loan Requirements
      </h5>',
    ];
        $loan_types = [
      '' => $this->t('Select Loan Type'),
    ];

    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('loan_type');

    foreach ($terms as $term) {
      $loan_types[$term->tid] = $term->name;
    }
        $form['loan']['field_loan_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type of Loan Applied For'),
      '#required' => TRUE,
      '#options' => $loan_types,
      '#attributes' => [
        'class' => ['form-control'],
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['loan']['field_required_amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Required Amount (₹)'),
      '#required' => TRUE,
      '#min' => 1,
      '#step' => 1,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'e.g. 1000000',
      ],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div>',
    ];
        $form['loan']['field_purpose_of_loan'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Purpose of Loan'),
      '#required' => TRUE,
      '#rows' => 3,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'Briefly describe why you need this loan',
      ],
      '#prefix' => '<div class="col-12">',
      '#suffix' => '</div>',
    ];
        /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */

    $form['documents'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['row', 'g-3', 'mt-2'],
      ],
      '#prefix' => '
      <h5 class="text-primary fw-bold mb-3 mt-4 border-bottom pb-2">
        Upload Documents
      </h5>',
    ];
    $form['documents']['field_pancard'] = [
    '#type' => 'managed_file',
    '#title' => $this->t('Upload PAN Card'),
    '#required' => TRUE,
    '#upload_location' => 'public://loan_documents/pan/',
    '#upload_validators' => [
        'file_validate_extensions' => ['pdf jpg jpeg png'],
    ],
    '#prefix' => '<div class="col-md-4">',
    '#suffix' => '</div>',
];
        $form['documents']['field_aadhar_card'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Aadhaar Card'),
      '#upload_location' => 'public://loan_documents/aadhar/',
      '#required' => TRUE,
      '#prefix' => '<div class="col-md-4">',
      '#suffix' => '</div>',
    ];
        $form['documents']['field_password_photo'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Photo'),
      '#upload_location' => 'public://loan_documents/photo/',
      '#required' => TRUE,
      '#prefix' => '<div class="col-md-4">',
      '#suffix' => '</div>',
    ];
 $form['actions'] = ['#type' => 'actions'];

$form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => $this->t('Submit Application'),
    '#attributes' => ['class' => ['btn btn-primary btn-lg']],
    '#button_type' => 'primary',
    '#submit' => ['::submitForm'],   // ← Yeh line add karo
];

    return $form;
  
 
  }
  /**
 * {@inheritdoc}
 */
public function validateForm(array &$form, FormStateInterface $form_state)
{
    // File validation (important)
    // $pan = $form_state->getValue(['documents', 'field_pancard']);
    // $aadhar = $form_state->getValue(['documents', 'field_aadhar_card']);
    // $photo = $form_state->getValue(['documents', 'field_password_photo']);

    // if (empty($pan[0])) {
    //     $form_state->setErrorByName('documents][field_pancard', $this->t('PAN Card is required.'));
    // }
    // if (empty($aadhar[0])) {
    //     $form_state->setErrorByName('documents][field_aadhar_card', $this->t('Aadhaar Card is required.'));
    // }
    // if (empty($photo[0])) {
    //     $form_state->setErrorByName('documents][field_password_photo', $this->t('Photo is required.'));
    // }

    // // Other basic checks
    // if (empty($form_state->getValue(['applicant', 'field_loan_email_address']))) {
    //     $form_state->setErrorByName('applicant][field_loan_email_address', $this->t('Email is required.'));
    // }
}

//     /**
//    * Running loan AJAX callback.
//    */
public function runningLoanCallback(array &$form, FormStateInterface $form_state)
{
    return $form['employment']['running_loan_wrapper'];
}
  /**
 * {@inheritdoc}
 */
/**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addWarning('submitForm() CALLED ho gaya!'); // Yeh dikhega toh pata chalega
    // dump($form_state); die();
  // Get uploaded file IDs.
  $pan = $form_state->getValue('field_pancard');
  $aadhar = $form_state->getValue('field_aadhar_card');
  $photo = $form_state->getValue('field_password_photo');

  // Mark uploaded files as permanent.
  foreach ([$pan, $aadhar, $photo] as $file_ids) {
    if (!empty($file_ids[0])) {
      if ($file = File::load($file_ids[0])) {
        $file->setPermanent();
        $file->save();
      }
    }
  }

  // Generate node title.
  $title = trim(
    $form_state->getValue('field_first_name') . ' ' .
    $form_state->getValue('field_last_name')
  ) . ' - Loan Application';

  // Create node.
  $node = Node::create([
    'type' => 'loan_section',
    'title' => $title,
    'status' => 1,
  ]);

  // Node owner & created time.
  $node->setOwnerId(\Drupal::currentUser()->id());
  $node->setCreatedTime(\Drupal::time()->getRequestTime());

  /*
  |--------------------------------------------------------------------------
  | Applicant Details
  |--------------------------------------------------------------------------
  */

  $node->set('field_first_name', $form_state->getValue('field_first_name'));
$node->set('field_last_name', $form_state->getValue('field_last_name'));
$node->set('field_father_s_name', $form_state->getValue('field_father_s_name'));
$node->set('field_mother_s_name', $form_state->getValue('field_mother_s_name'));
$node->set('field_date_of_birth', $form_state->getValue('field_date_of_birth'));
$node->set('field_gender', $form_state->getValue('field_gender'));
$node->set('field_caste_category', $form_state->getValue('field_caste_category'));
$node->set('field_loan_email_address', $form_state->getValue('field_loan_email_address'));
$node->set('field_phone_number', $form_state->getValue('field_phone_number'));

/*
|--------------------------------------------------------------------------
| Address Details
|--------------------------------------------------------------------------
*/

$node->set('field_current_address', $form_state->getValue('field_current_address'));
$node->set('field_permanent_address', $form_state->getValue('field_permanent_address'));

/*
|--------------------------------------------------------------------------
| Employment Details
|--------------------------------------------------------------------------
*/

$node->set('field_employee_type', $form_state->getValue('field_employee_type'));
$node->set('field_annual_income', $form_state->getValue('field_annual_income'));
$node->set('field_current_running_loan', $form_state->getValue('field_current_running_loan'));

// if ($form_state->getValue('field_current_running_loan') == 'Yes') {
//   $node->set(
//     'field_if_yes_loan_amount',
//     $form_state->getValue('field_if_yes_loan_amount')
//   );
// }

/*
|--------------------------------------------------------------------------
| Loan Details
|--------------------------------------------------------------------------
*/

$node->set('field_loan_type', [
  'target_id' => $form_state->getValue('field_loan_type'),
]);

$node->set('field_required_amount', $form_state->getValue('field_required_amount'));
$node->set('field_purpose_of_loan', $form_state->getValue('field_purpose_of_loan'));

  /*
  |--------------------------------------------------------------------------
  | Documents
  |--------------------------------------------------------------------------
  */

  if (!empty($pan[0])) {
    $node->set('field_pancard', [
      'target_id' => $pan[0],
      'alt' => 'PAN Card',
    ]);
  }

  if (!empty($aadhar[0])) {
    $node->set('field_aadhar_card', [
      'target_id' => $aadhar[0],
      'alt' => 'Aadhar Card',
    ]);
  }

  if (!empty($photo[0])) {
    $node->set('field_password_photo', [
      'target_id' => $photo[0],
      'alt' => 'Passport Photo',
    ]);
  }

  try {

    // Save node.
    $node->save();

    // Register file usage.
    $file_usage = \Drupal::service('file.usage');

    foreach ([$pan, $aadhar, $photo] as $file_ids) {
      if (!empty($file_ids[0])) {
        if ($file = File::load($file_ids[0])) {
          $file_usage->add($file, 'partner_dashboard', 'node', $node->id());
        }
      }
    }

    $this->messenger()->addStatus(
      $this->t('Your loan application has been submitted successfully.')
    );

    $form_state->setRedirect('<front>');

  }
  catch (\Exception $e) {

    \Drupal::logger('partner_dashboard')->error($e->getMessage());

    $this->messenger()->addError(
      $this->t('Something went wrong while submitting your application. Please try again.')
    );
  }

}

}