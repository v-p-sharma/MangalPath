<?php
namespace Drupal\partner_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class Vehicle_selling_rental extends FormBase
{
    public function getFormId()
    {
        return 'vehicle_selling_rental_form';
    }

    /* =========================================================
     * STEP MANAGER
     * ========================================================= */

    private function getCurrentStep(FormStateInterface $form_state)
    {
        return $form_state->get('step') ?? 1;
    }

    private function setStep(FormStateInterface $form_state, $step)
    {
        $form_state->set('step', $step);
    }

    /* =========================================================
     * STEP STORAGE (FIXED SAFE MERGE)
     * ========================================================= */

    private function store(FormStateInterface $form_state)
    {
        $values = $form_state->getValues();
        $stored = $form_state->get('vehicle_data') ?? [];

        foreach ($values as $key => $value) {
            if ($key !== 'op' && $key !== 'step') {
                $stored[$key] = $value;
            }
        }

        $form_state->set('vehicle_data', $stored);
    }

    /* =========================================================
     * BUILD STEP WRAPPER (UNCHANGED STRUCTURE)
     * ========================================================= */

    protected function build_step_card($step_id, $step_title, array $fields)
    {
        return [
            '#type'       => 'container',
            '#attributes' => [
                'class' => ['step-card', 'active', 'bg-white', 'rounded-xl', 'shadow-card', 'p-6', 'sm:p-8', 'mb-6'],
                'id'    => $step_id,
            ],

            'title'       => [
                '#markup' => '<h2 class="text-lg font-bold text-primaryDark mb-6 border-l-4 border-accent pl-3">' . $step_title . '</h2>',
            ],

            'fields'      => [
                '#type'       => 'container',
                '#attributes' => [
                    'class' => ['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-6'],
                ],
            ] + $fields,
        ];
    }

    /* =========================================================
     * FORM BUILD ENTRY
     * ========================================================= */

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $step = $this->getCurrentStep($form_state);

        \Drupal::logger('vehicle')->notice('Current Step: @step', [
            '@step' => $step,
        ]);
        // $form['vehicle_form_wrapper'] = [
        //     '#type'       => 'container',
        //     '#attributes' => [
        //         'id' => 'vehicle-form-wrapper',
        //     ],
        // ];
        $step = $this->getCurrentStep($form_state);

        $form['#attributes']['enctype'] = 'multipart/form-data';

        if (! $form_state->get('vehicle_data')) {
            $form_state->set('vehicle_data', []);
        }

        $stored = $form_state->get('vehicle_data');

        // WRAPPER (IMPORTANT FOR AJAX LATER)
        $form['vehicle_form_wrapper'] = [
            '#type'       => 'container',
            '#attributes' => [
                'id' => 'vehicle-form-wrapper',
            ],
        ];

        /* =========================================================
         * STEP ROUTING
         * ========================================================= */

        if ($step == 1) {
            $form['vehicle_form_wrapper']['step'] = $this->step1($stored, $form_state);
        }

        if ($step == 2) {
            $form['vehicle_form_wrapper']['step'] = $this->step2($stored, $form_state);
        }

        if ($step == 3) {
            $form['vehicle_form_wrapper']['step'] = $this->step3($stored, $form_state);
        }

        if ($step == 4) {
            $form['vehicle_form_wrapper']['step'] = $this->step4($stored, $form_state);
        }

        if ($step == 5) {
            $form['vehicle_form_wrapper']['step'] = $this->step5($stored, $form_state);
        }

        if ($step == 6) {
            $form['vehicle_form_wrapper']['step'] = $this->step6($stored, $form_state);
        }

        if ($step == 7) {
            $form['vehicle_form_wrapper']['step'] = $this->step7($stored, $form_state);
        }

        if ($step == 8) {
            $form['vehicle_form_wrapper']['step'] = $this->step8($stored, $form_state);
        }

        if ($step == 9) {
            $form['vehicle_form_wrapper']['step'] = $this->step9($stored, $form_state);
        }

        if ($step == 10) {
            $form['vehicle_form_wrapper']['step'] = $this->step10($stored, $form_state);
        }

        /* =========================================================
         * NAVIGATION
         * ========================================================= */

        $form['actions'] = [
            '#type' => 'actions',
        ];

        $form['actions']['prev'] = [
            '#type'   => 'submit',
            '#value'  => 'Previous',
            '#submit' => ['::prevStep'],
            // '#ajax'                    => [
            //     'callback' => '::ajaxRefreshForm',
            //     'wrapper'  => 'vehicle-form-wrapper',
            // ],
            // '#limit_validation_errors' => [], // Important for prev
        ];

        $is_last_step = ($step >= 10);

        $form['actions']['next'] = [
            '#type'   => 'submit',
            '#value'  => $is_last_step ? 'Submit' : 'Next',
            '#submit' => ['::handleNextSubmit'], // ← SINGLE HANDLER
                                                 // '#ajax'        => $is_last_step ? null : [
                                                 //     'callback' => '::ajaxRefreshForm',
                                                 //     'wrapper'  => 'vehicle-form-wrapper',
                                                 // ],
                                                 // '#button_type' => $is_last_step ? 'primary' : 'button',
        ];

        return $form;
    }

    private function step1($stored, FormStateInterface $form_state)
    {
        // For AJAX rebuild: read submitted parent value first.
        $parent_tid = $form_state->getUserInput()['field_vehical_type_parent'] ?? $stored['field_vehical_type_parent'] ?? $form_state->get('vehicle_data')['field_vehical_type_parent'] ?? '';

        return $this->build_step_card('step-1', 'Vehicle Basic Details', [

            'field_vehicle_purpose'     => [
                '#type'          => 'select',
                '#title'         => 'Vehicle Purpose',
                '#required'      => true,
                '#options'       => $this->getPurposeOptions(),
                '#default_value' => $stored['field_vehicle_purpose'] ?? '',
            ],

            'title'                     => [
                '#type'          => 'textfield',
                '#title'         => 'Listing Title',
                '#required'      => true,
                '#default_value' => $stored['title'] ?? '',
                '#attributes'    => [
                    'class' => ['form-input', 'w-full', 'px-4', 'py-3', 'rounded-lg', 'border', 'border-border'],
                ],
            ],

            /* VEHICLE TYPE (FIXED: NO AUTOCOMPLETE ISSUE LATER FIXED IN STEP 12 LOGIC) */
            'field_vehical_type_parent' => [
                '#type'          => 'select',
                '#title'         => 'Vehicle Category',
                '#options'       => $this->getVehicleParentCategories(),
                '#default_value' => $parent_tid,

                '#ajax'          => [
                    'callback' => '::loadChildVehicleTypes',
                    'wrapper'  => 'vehicle-child-wrapper',
                    'event'    => 'change',
                ],
            ],

            'vehicle_child_wrapper'     => [
                '#type'              => 'container',
                '#attributes'        => [
                    'id' => 'vehicle-child-wrapper',
                ],

                'field_vehical_type' => [
                    '#type'          => 'select',
                    '#title'         => 'Vehicle Type',
                    '#options'       => $this->getVehicleChildCategories($parent_tid),
                    '#default_value' => $stored['field_vehical_type'] ?? '',
                ],
            ],

            'field_vehicle_brand'       => [
                '#type'          => 'textfield',
                '#title'         => 'Brand',
                '#required'      => true,
                '#default_value' => $stored['field_vehicle_brand'] ?? '',
            ],

            'field_model'               => [
                '#type'          => 'textfield',
                '#title'         => 'Model',
                '#required'      => true,
                '#default_value' => $stored['field_model'] ?? '',
            ],

            'field_variant'             => [
                '#type'          => 'textfield',
                '#title'         => 'Variant',
                '#default_value' => $stored['field_variant'] ?? '',
            ],

            'field_manufacturing_year'  => [
                '#type'          => 'date',
                '#title'         => 'Manufacturing Year',
                '#required'      => true,
                '#default_value' => $stored['field_manufacturing_year'] ?? '',
            ],

            'field_registration_year'   => [
                '#type'          => 'date',
                '#title'         => 'Registration Year',
                '#required'      => true,
                '#default_value' => $stored['field_registration_year'] ?? '',
            ],

            'body'                      => [
                '#type'          => 'textarea',
                '#title'         => 'Vehicle Description',
                '#default_value' => $stored['body'] ?? '',
            ],
        ]);
    }

    private function step2($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-2', 'Vehicle Identification', [

            'field_registration_number'   => [
                '#type'          => 'textfield',
                '#title'         => 'Registration Number',
                '#required'      => true,
                '#default_value' => $stored['field_registration_number'] ?? '',
            ],

            /* CHASSIS NUMBER (FIXED MISNAMED FIELD) */
            'field_width_ft'              => [
                '#type'          => 'textfield',
                '#title'         => 'Chassis Number',
                '#default_value' => $stored['field_width_ft'] ?? '',
            ],

            /* ENGINE NUMBER */
            'field_whatsapp_number'       => [
                '#type'          => 'textfield',
                '#title'         => 'Engine Number',
                '#default_value' => $stored['field_whatsapp_number'] ?? '',
            ],

            /* VIN */
            'field_village'               => [
                '#type'          => 'textfield',
                '#title'         => 'VIN Number',
                '#default_value' => $stored['field_village'] ?? '',
            ],

            /* RC AVAILABLE */
            'field_electricity_available' => [
                '#type'          => 'radios',
                '#title'         => 'RC Available?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_electricity_available'] ?? 0,
            ],

            /* INSURANCE */
            'field_loan_available'        => [
                '#type'          => 'radios',
                '#title'         => 'Insurance Available?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_loan_available'] ?? 0,
            ],

            /* PUC */
            'field_mutation_available'    => [
                '#type'          => 'radios',
                '#title'         => 'PUC Certificate?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_mutation_available'] ?? 0,
            ],
        ]);
    }

    private function step3($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-3', 'Vehicle Specifications', [

            /* ---------------------------
         * FUEL TYPE (FIXED TAXONOMY SAFE SELECT)
         * --------------------------- */
            'field_fuel_type'          => [
                '#type'          => 'select',
                '#title'         => 'Fuel Type',
                '#options'       => $this->getFuelTypeOptions(),
                '#default_value' => $this->normalizeTerm($stored['field_fuel_type'] ?? null),
                '#attributes'    => [
                    'class' => ['w-full', 'px-4', 'py-3', 'rounded-lg', 'border', 'border-border'],
                ],
            ],

            /* ---------------------------
         * TRANSMISSION
         * --------------------------- */
            'field_transmission'       => [
                '#type'          => 'select',
                '#title'         => 'Transmission',
                '#options'       => [
                    'manual'    => 'Manual',
                    'automatic' => 'Automatic',
                    'cvt'       => 'CVT',
                ],
                '#default_value' => $stored['field_transmission'] ?? '',
            ],

            /* ---------------------------
         * ENGINE CAPACITY
         * --------------------------- */
            'field_engine_capacity_cc' => [
                '#type'          => 'number',
                '#title'         => 'Engine Capacity (CC)',
                '#default_value' => $stored['field_engine_capacity_cc'] ?? '',
                '#attributes'    => [
                    'placeholder' => 'e.g. 1197',
                ],
            ],

            /* ---------------------------
         * MILEAGE
         * --------------------------- */
            'field_mileage_km_l'       => [
                '#type'          => 'number',
                '#title'         => 'Mileage (KM/L)',
                '#default_value' => $stored['field_mileage_km_l'] ?? '',
            ],

            /* ---------------------------
         * BATTERY CAPACITY (EV ONLY)
         * --------------------------- */
            'field_total_area'         => [
                '#type'          => 'textfield',
                '#title'         => 'Battery Capacity (kWh)',
                '#default_value' => $stored['field_total_area'] ?? '',
            ],

            /* ---------------------------
         * RANGE (EV ONLY)
         * --------------------------- */
            'field_survey_number'      => [
                '#type'          => 'number',
                '#title'         => 'Range (KM)',
                '#default_value' => $stored['field_survey_number'] ?? '',
            ],

            /* ---------------------------
         * COLOR
         * --------------------------- */
            'field_super_area'         => [
                '#type'          => 'textfield',
                '#title'         => 'Color',
                '#default_value' => $stored['field_super_area'] ?? '',
            ],

            /* ---------------------------
         * SEATING CAPACITY
         * --------------------------- */
            'field_state'              => [
                '#type'          => 'number',
                '#title'         => 'Seating Capacity',
                '#default_value' => $stored['field_state'] ?? '',
            ],

            /* ---------------------------
         * DOORS
         * --------------------------- */
            'field_soil_type'          => [
                '#type'          => 'number',
                '#title'         => 'Number of Doors',
                '#default_value' => $stored['field_soil_type'] ?? '',
            ],

            /* ---------------------------
         * DRIVE TYPE
         * --------------------------- */
            'field_drive_type'         => [
                '#type'          => 'select',
                '#title'         => 'Drive Type',
                '#options'       => [
                    'fwd_front_wheel_drive' => 'FWD',
                    'rwd_rear_wheel_drive' => 'RWD',
                    'awd_all_wheel_drive' => 'AWD',
                ],
                '#default_value' => $stored['field_drive_type'] ?? '',
            ],

        ]);
    }
    private function getFuelTypeOptions()
    {
        $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadTree('vehicle_feul_type');

        $options = [];

        foreach ($terms as $term) {
            $options[$term->tid] = $term->name;
        }

        return $options;
    }

    private function normalizeTerm($value)
    {
        if (is_array($value)) {
            return reset($value);
        }
        if ($value instanceof \Drupal\taxonomy\Entity\Term) {
            return $value->id();
        }
        return $value;
    }
    private function step4($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-4', 'Vehicle Condition', [

            /* ---------------------------
         * VEHICLE CONDITION
         * --------------------------- */
            'field_vehicle_condition'   => [
                '#type'          => 'select',
                '#title'         => 'Vehicle Condition',
                '#required'      => true,
                '#options'       => [
                    ''          => 'Select Condition',
                    'new'       => 'New',
                    'like_new'  => 'Like New',
                    'excellent' => 'Excellent',
                    'good'      => 'Good',
                    'average'   => 'Average',
                ],
                '#default_value' => $stored['field_vehicle_condition'] ?? '',
            ],

            /* ---------------------------
         * ODOMETER
         * --------------------------- */
            'field_odometer_reading_km' => [
                '#type'          => 'number',
                '#title'         => 'Odometer Reading (KM)',
                '#required'      => true,
                '#default_value' => $stored['field_odometer_reading_km'] ?? '',
            ],

            /* ---------------------------
         * NUMBER OF OWNERS
         * --------------------------- */
            'field_security_deposit'    => [
                '#type'          => 'select',
                '#title'         => 'Number of Previous Owners',
                '#options'       => [
                    '1st'  => '1st Owner',
                    '2nd'  => '2nd Owner',
                    '3rd'  => '3rd Owner',
                    '4th+' => '4th+ Owner',
                ],
                '#default_value' => $stored['field_security_deposit'] ?? '',
            ],

            /* ---------------------------
         * ACCIDENT HISTORY (FIXED MEANING)
         * --------------------------- */
            'field_power_backup'        => [
                '#type'          => 'radios',
                '#title'         => 'Accidental History?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_power_backup'] ?? 0,
            ],

            /* ---------------------------
         * FLOOD DAMAGE (FIXED FIELD NAME)
         * --------------------------- */
            'field_price_negotiable'    => [
                '#type'          => 'radios',
                '#title'         => 'Flood Damage?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_price_negotiable'] ?? 0,
            ],

            /* ---------------------------
         * SERVICE HISTORY
         * --------------------------- */
            'field_registry_available'  => [
                '#type'          => 'radios',
                '#title'         => 'Service History Available?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_registry_available'] ?? 0,
            ],

            /* ---------------------------
         * WARRANTY
         * --------------------------- */
            'field_road_access'         => [
                '#type'          => 'radios',
                '#title'         => 'Warranty Available?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_road_access'] ?? 0,
            ],

        ]);
    }

    private function step5($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-5', 'Price Details', [

            /* ---------------------------
         * EXPECTED PRICE
         * --------------------------- */
            'field_expected_selling_price'   => [
                '#type'          => 'number',
                '#title'         => 'Expected Selling Price (₹)',
                '#required'      => true,
                '#default_value' => $stored['field_expected_selling_price'] ?? '',
                '#attributes'    => [
                    'placeholder' => 'e.g. 550000',
                ],
            ],

            /* ---------------------------
         * PRICE NEGOTIABLE
         * --------------------------- */
            'field_vehicle_price_negotiable' => [
                '#type'          => 'radios',
                '#title'         => 'Price Negotiable?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_vehicle_price_negotiable'] ?? 0,
            ],

            /* ---------------------------
         * LOAN AVAILABLE
         * --------------------------- */
            'field_vehicle_loan_available'   => [
                '#type'          => 'radios',
                '#title'         => 'Loan Available?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_vehicle_loan_available'] ?? 0,
            ],

            /* ---------------------------
         * EMI AVAILABLE
         * --------------------------- */
            'field_emi_available'            => [
                '#type'          => 'radios',
                '#title'         => 'EMI Available?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_emi_available'] ?? 0,
            ],

            /* ---------------------------
         * EXCHANGE POSSIBLE
         * --------------------------- */
            'field_exchange_possible'        => [
                '#type'          => 'radios',
                '#title'         => 'Exchange Possible?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_exchange_possible'] ?? 0,
            ],

        ]);
    }

    private function step6($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-6', 'Commercial Vehicle Details', [

            'field_load_capacity_tons'   => [
                '#type'          => 'textfield',
                '#title'         => 'Load Capacity (Tons)',
                '#default_value' => $stored['field_load_capacity_tons'] ?? '',
            ],

            'field_permit_type'          => [
                '#type'          => 'select',
                '#title'         => 'Permit Type',
                '#options'       => [
                    'goods_carrier'     => 'Goods Carrier',
                    'passenger' => 'Passenger',
                    'private'   => 'Private',
                ],
                '#default_value' => $stored['field_permit_type'] ?? '',
            ],

            'field_national_permit'      => [
                '#type'          => 'radios',
                '#title'         => 'National Permit?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_national_permit'] ?? 0,
            ],

            'field_fitness_certificate'  => [
                '#type'          => 'radios',
                '#title'         => 'Fitness Certificate?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_fitness_certificate'] ?? 0,
            ],

            'field_commercial_insurance' => [
                '#type'          => 'radios',
                '#title'         => 'Commercial Insurance?',
                '#options'       => [1 => 'Yes', 0 => 'No'],
                '#default_value' => $stored['field_commercial_insurance'] ?? 0,
            ],

        ]);
    }

    private function step7($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-7', 'Document Uploads', [

            'field_rc'                         => [
                '#type'            => 'managed_file',
                '#title'           => 'RC Copy',
                '#upload_location' => 'public://vehicle-documents/',
                '#default_value'   => $stored['field_rc'] ?? null,
            ],

            'field_insurance_copy'             => [
                '#type'            => 'managed_file',
                '#title'           => 'Insurance Copy',
                '#upload_location' => 'public://vehicle-documents/',
                '#default_value'   => $stored['field_insurance_copy'] ?? null,
            ],

            'field_puc_certificate'            => [
                '#type'            => 'managed_file',
                '#title'           => 'PUC Certificate',
                '#upload_location' => 'public://vehicle-documents/',
                '#default_value'   => $stored['field_puc_certificate'] ?? null,
            ],

            'field_other_documents_noc_transf' => [
                '#type'            => 'managed_file',
                '#title'           => 'Other Documents',
                '#multiple'        => true,
                '#upload_location' => 'public://vehicle-documents/',
                '#default_value'   => $stored['field_other_documents_noc_transf'] ?? null,
            ],
        ]);
    }
    private function step8($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-8', 'Vehicle Media', [

            'field_featured_image'   => [
                '#type'            => 'managed_file',
                '#title'           => 'Featured Image',
                '#required'        => true,
                '#upload_location' => 'public://vehicle-images/',
                '#default_value'   => $stored['field_featured_image'] ?? null,
            ],

            'field_property_gallery' => [
                '#type'            => 'managed_file',
                '#title'           => 'Gallery',
                '#multiple'        => true,
                '#upload_location' => 'public://vehicle-images/',
                '#default_value'   => $stored['field_property_gallery'] ?? null,
            ],
        ]);
    }
    private function step9($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-9', 'Location Details', [

            'field_country'               => [
                '#type'          => 'textfield',
                '#title'         => 'Country',
                '#default_value' => $stored['field_country'] ?? 'India',
                '#attributes'    => ['readonly' => true],
            ],

            'field_road_width_ft'         => [
                '#type'          => 'textfield',
                '#title'         => 'State',
                '#default_value' => $stored['field_road_width_ft'] ?? '',
            ],

            'field_district'              => [
                '#type'          => 'textfield',
                '#title'         => 'District',
                '#default_value' => $stored['field_district'] ?? '',
            ],

            'field_city'                  => [
                '#type'          => 'textfield',
                '#title'         => 'City',
                '#required'      => true,
                '#default_value' => $stored['field_city'] ?? '',
            ],

            'field_carpet_area'           => [
                '#type'          => 'textfield',
                '#title'         => 'Area / Locality',
                '#default_value' => $stored['field_carpet_area'] ?? '',
            ],

            'field_pincode'               => [
                '#type'          => 'textfield',
                '#title'         => 'Pincode',
                '#default_value' => $stored['field_pincode'] ?? '',
            ],

            'field_vehi_complete_address' => [
                '#type'          => 'textarea',
                '#title'         => 'Complete Address',
                '#default_value' => $stored['field_vehi_complete_address'] ?? '',
            ],
        ]);
    }
    private function step10($stored, FormStateInterface $form_state)
    {
        return $this->build_step_card('step-10', 'Seller Details', [

            'field_seller_name'          => [
                '#type'          => 'textfield',
                '#title'         => 'Seller Name',
                '#required'      => true,
                '#default_value' => $stored['field_seller_name'] ?? '',
            ],

            'field_property_highlights'  => [
                '#type'          => 'textfield',
                '#title'         => 'Dealer Name',
                '#default_value' => $stored['field_property_highlights'] ?? '',
            ],

            'field_contact_number'       => [
                '#type'          => 'tel',
                '#title'         => 'Contact Number',
                '#required'      => true,
                '#default_value' => $stored['field_contact_number'] ?? '',
            ],

            'field_owner_name'           => [
                '#type'          => 'textfield',
                '#title'         => 'WhatsApp Number',
                '#default_value' => $stored['field_owner_name'] ?? '',
            ],

            'field_email_address'        => [
                '#type'          => 'email',
                '#title'         => 'Email',
                '#default_value' => $stored['field_email_address'] ?? '',
            ],

            'field_best_time_to_contact' => [
                '#type'          => 'select',
                '#title'         => 'Best Time',
                '#options'       => [
                    'anytime' => 'Anytime',
                    'morning_9am_12pm' => 'Morning (9AM - 12PM)',
                    'afternoon_12pm_5pm' => 'Afternoon (12PM - 5PM)',
                     'evening_5pm_9pm' => 'Evening (5PM - 9PM)'
                ],
                '#default_value' => $stored['field_best_time_to_contact'] ?? '',
            ],

            'field_seller_type'          => [
                '#type'          => 'select',
                '#title'         => 'Seller Type',
                '#options'       => [
                    'individual_owner'    => 'Owner',
                    'dealer'   => 'Dealer',
                    'broker'   => 'Broker',
                    'showroom' => 'Showroom',
                ],
                '#default_value' => $stored['field_seller_type'] ?? '',
            ],
        ]);
    }
    public function finalSubmit(array &$form, FormStateInterface $form_state)
    {
        $this->store($form_state);
        $data = $form_state->get('vehicle_data') ?? [];
        // print_r("hep hep hure final step called");
        // Merge last step values
        $data = array_merge($data, $form_state->getValues());

        $node = Node::create([
            'type'  => 'vehicle_sell',
            'title' => $data['title'] ?? 'Vehicle Listing',
            'uid' => \Drupal::currentUser()->id(),
            'status' => 0, // Unpublished
        ]);

        foreach ($data as $key => $value) {

            if (strpos($key, 'field_') === 0 && ! empty($value)) {
                // dump($key);
                if (! $node->hasField($key)) {
                    continue;
                }
                // dump($key);die();
                // FIX: managed_file conversion
                if (is_array($value)) {
                    $node->set($key, array_values($value));
                } else {
                    $node->set($key, $value);
                }
            }
        }

        // IMPORTANT: MAKE FILES PERMANENT
        $this->makeFilesPermanent($data);

        $node->save();

        $this->messenger()->addMessage('Vehicle submitted successfully!');


        $form_state->set('vehicle_data', []);
        $form_state->setRedirect(
            'mangalpath_payment.payment',
            ['node' => $node->id()]
        );
    }
    private function makeFilesPermanent($data)
    {
        $file_fields = [
            'field_rc',
            'field_insurance_copy',
            'field_puc_certificate',
            'field_other_documents_noc_transf',
            'field_featured_image',
            'field_property_gallery',
        ];

        foreach ($file_fields as $field) {
            if (! empty($data[$field])) {
                $fids = (array) $data[$field];

                foreach ($fids as $fid) {
                    $file = \Drupal\file\Entity\File::load($fid);
                    if ($file) {
                        $file->setPermanent();
                        $file->save();
                    }
                }
            }
        }
    }
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        // Basic step-level required checks so errors show immediately.
        // Drupal will already show element errors, but this ensures missing values
        // from multi-step AJAX flows are caught.
        $values = $form_state->getValues();

        // Step 1
        if ($this->getCurrentStep($form_state) == 1) {
            if (empty($values['field_vehical_type_parent'])) {
                $form_state->setErrorByName('field_vehical_type_parent', $this->t('Please select Vehicle Category.'));
            }
            if (empty($values['field_vehical_type'])) {
                $form_state->setErrorByName('field_vehical_type', $this->t('Please select Vehicle Type.'));
            }
        }
        if ($form_state->getTriggeringElement()['#value'] == 'Submit') {
            // die('VALIDATE');
        }

        // Step 10 (example validation for seller fields)
        if ($this->getCurrentStep($form_state) == 10) {
            if (empty($values['field_seller_name'])) {
                $form_state->setErrorByName('field_seller_name', $this->t('Seller Name is required.'));
            }
            if (empty($values['field_contact_number'])) {
                $form_state->setErrorByName('field_contact_number', $this->t('Contact Number is required.'));
            }
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Required by Drupal.
    }

    // public function ajaxRefreshForm(array &$form, FormStateInterface $form_state)
    // {
    //     return $form['vehicle_form_wrapper'];
    // }
    private function getPurposeOptions()
    {
        $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadTree('purpose_of_vehicle');

        $options = [
            '' => '- Select Purpose -',
        ];
        foreach ($terms as $term) {
            $options[$term->tid] = $term->name;
        }

        return $options;
    }
    public function loadChildVehicleTypes(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRebuild(true);

        return $form['vehicle_form_wrapper']['step']['fields']['vehicle_child_wrapper'];
    }

    // Used by $form['actions'][...]['#ajax']['callback'].
    // Must exist, otherwise Drupal throws: "#ajax callback is empty or not callable".
    // public function ajaxRefreshForm(array &$form, FormStateInterface $form_state)
    // {
    //     return $form['vehicle_form_wrapper'];
    // }
    private function getVehicleParentCategories()
    {
        $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadTree('vehicle_category_for_sell', 0, 1);

        $options = [
            '' => '- Select Category -',
        ];

        foreach ($terms as $term) {
            $options[$term->tid] = $term->name;
        }

        return $options;
    }

    private function getVehicleChildCategories($parent_tid)
    {
        $options = [];

        if (empty($parent_tid)) {
            return $options;
        }

        $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadTree('vehicle_category_for_sell', $parent_tid, 1);

        foreach ($terms as $term) {
            $options[$term->tid] = $term->name;
        }

        return $options;
    }
    public function prevStep(array &$form, FormStateInterface $form_state)
    {
        $this->store($form_state);

        $step = $this->getCurrentStep($form_state);

        if ($step > 1) {
            $this->setStep($form_state, $step - 1);
        }

        $form_state->setRebuild(true);
    }
    public function nextStep(array &$form, FormStateInterface $form_state)
    {
        $this->store($form_state);

        $step = $this->getCurrentStep($form_state);

        if ($step < 11) {
            $this->setStep($form_state, $step + 1);
        }

        $form_state->setRebuild(true);
    }
    public function handleNextSubmit(array &$form, FormStateInterface $form_state)
    {
        $this->store($form_state); // Always save current step data

        $step    = $this->getCurrentStep($form_state);
        $trigger = $form_state->getTriggeringElement();

        if ($step >= 10 || ($trigger['#value'] ?? '') === 'Submit') {
            $this->finalSubmit($form, $form_state);
        } else {
            // Normal next step
            $this->setStep($form_state, $step + 1);
            $form_state->setRebuild(true);
        }
    }
}
