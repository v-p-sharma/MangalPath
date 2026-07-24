<?php
namespace Drupal\partner_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

/**
 * Provides a dynamic Property form.
 */
class Property_selling extends FormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'property_selling_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form['#theme'] = 'property_basic_form';
        // Uses theme hook to render twig. Scroll fix is in css + property_basic_form.twig content.
        // $node = \Drupal::entityTypeManager()->getStorage('node')->load(2);
        // dump($node); die();
        // Property Basic Details............
        $form['propTitle'] = [
            '#type'       => 'textfield',
            '#required'   => true,
            '#attributes' => ['placeholder' => 'e.g. Luxury 3BHK Flat in Civil Lines'],
        ];

        $form['propPurpose'] = [
            '#type'     => 'select',
            '#required' => true,
            '#options'  => [
                ''   => $this->t('Select Purpose'),
                '72' => $this->t('Sell'),
                '73' => $this->t('Rent'),
            ],
        ];

        $form['propCategory'] = [
            '#type'     => 'select',
            // '#title' => $this->t('Property Category'),
            '#required' => true,
            '#options'  => $this->getPropertyCategories(),
            '#ajax'     => [
                'callback' => '::updatePropertyTypes',
                'event'    => 'change',
                'wrapper'  => 'property-type-wrapper',
            ],
        ];

        $form['propTypeWrapper'] = [
            '#type'       => 'container',
            '#attributes' => ['id' => 'property-type-wrapper'],
        ];

        $selected_category = $form_state->getValue('propCategory');

        if (! $selected_category) {
            $selected_category = $form_state->getUserInput()['propCategory'] ?? null;
        }
// For AJAX rebuild, always read category from submitted user input first.
        $selected_category = $form_state->getUserInput()['propCategory'] ?? $selected_category;
        $optionsData  = $this->getPropertyTypes($selected_category);
        $form['propTypeWrapper']['propType'] = [
            '#type'          => 'select',
            '#required'      => true,
            '#empty_option'  => $this->t('Select Property Type'),
            '#options'       => $optionsData,
            '#default_value' => $form_state->getUserInput()['propType'] ?? null,
        ];

        $form['propDescription'] = [
            '#type'       => 'textarea',
            '#attributes' => ['placeholder' => 'Describe your property in detail...'],
        ];

        $form['propHighlights'] = [
            '#type'       => 'textarea',
            '#attributes' => ['placeholder' => 'Key highlights (e.g. Near Metro, Park View)...'],
        ];
        // Location Details..................
        $form['country'] = [
            '#type'       => 'textfield',
            '#attributes' => ['placeholder' => 'India'],
        ];

        $form['state'] = [
            '#type'     => 'textfield',
            '#required' => true,
        ];

        $form['district'] = [
            '#type' => 'textfield',
        ];

        $form['city'] = [
            '#type'     => 'textfield',
            '#required' => true,
        ];

        $form['locality'] = [
            '#type'     => 'textfield',
            '#required' => true,
        ];

        $form['village'] = [
            '#type' => 'textfield',
        ];

        $form['landmark'] = [
            '#type' => 'textfield',
        ];

        $form['pincode'] = [
            '#type'       => 'textfield',
            '#required'   => true,
            '#attributes' => ['pattern' => '[0-9]{6}'],
        ];

        $form['address'] = [
            '#type'       => 'textarea',
            '#attributes' => ['rows' => 2],
        ];

        $form['latitude'] = [
            '#type'       => 'textfield',
            '#attributes' => ['placeholder' => 'e.g. 26.9124'],
        ];

        $form['longitude'] = [
            '#type'       => 'textfield',
            '#attributes' => ['placeholder' => 'e.g. 75.7873'],
        ];

        $form['fetch_location'] = [
            '#type'       => 'button',
            '#value'      => $this->t('Fetch Current Location'),
            '#attributes' => [
                'class' => ['btn', 'btn-secondary'],
                'style' => 'width:100%',
            ],
        ];
// Property Size Details...............

        $form['areaUnit'] = [
            '#type'     => 'select',
            '#required' => true,
            '#options'  => [
                'sq_ft'   => $this->t('Sq Ft'),
                'sq_yard' => $this->t('Sq Yard'),
                'acre'    => $this->t('Acre'),
                'hectare' => $this->t('Hectare'),
                'bigha'   => $this->t('Bigha'),
                'biswa'   => $this->t('Biswa'),
                'kanal'   => $this->t('Kanal'),
                'marla'   => $this->t('Marla'),
            ],
        ];

        $form['totalArea'] = [
            '#type'     => 'number',
            '#required' => true,
        ];

        $form['builtupArea'] = [
            '#type' => 'number',
        ];

        $form['carpetArea'] = [
            '#type' => 'number',
        ];

        $form['superArea'] = [
            '#type' => 'number',
        ];

        $form['frontWidth'] = [
            '#type' => 'number',
        ];

        $form['roadWidth'] = [
            '#type' => 'number',
        ];

        $form['length'] = [
            '#type' => 'number',
        ];

        $form['width'] = [
            '#type' => 'number',
        ];

        $form['cornerProperty'] = [
            '#type'    => 'select',
            '#options' => [
                'no'  => $this->t('No'),
                'yes' => $this->t('Yes'),
            ],
        ];
//  Residential Details .........................

        $form['bhkType'] = [
            '#type'    => 'select',
            '#options' => [
                '1rk'         => $this->t('1 RK'),
                'single_room' => $this->t("single_room"),
                '1_bhk'       => $this->t('1 BHK'),
                '2_bhk'       => $this->t('2 BHK'),
                '3_bhk'       => $this->t('3 BHK'),
                '4_bhk'       => $this->t('4 BHK'),
                '5_bhk'       => $this->t('5+ BHK'),
            ],
        ];

        $form['bedrooms'] = [
            '#type' => 'number',
        ];

        $form['bathrooms'] = [
            '#type' => 'number',
        ];

        $form['balconies'] = [
            '#type' => 'number',
        ];

        $form['kitchen'] = [
            '#type' => 'number',
        ];

        $form['drawingRoom'] = [
            '#type' => 'number',
        ];

        $form['diningRoom'] = [
            '#type' => 'number',
        ];

        $form['storeRoom'] = [
            '#type' => 'number',
        ];

        $form['servantRoom'] = [
            '#type' => 'number',
        ];

        $form['poojaRoom'] = [
            '#type'    => 'select',
            '#options' => [
                'no'  => $this->t('No'),
                'yes' => $this->t('Yes'),
            ],
        ];

        $form['studyRoom'] = [
            '#type'    => 'select',
            '#options' => [
                'no'  => $this->t('No'),
                'yes' => $this->t('Yes'),
            ],
        ];

        $form['floorNumber'] = [
            '#type' => 'number',
        ];

        $form['totalFloors'] = [
            '#type' => 'number',
        ];

        $form['propertyFacing'] = [
            '#type'    => 'select',
            '#options' => [
                ''      => $this->t('Select Facing'),
                'east'  => $this->t('East'),
                'west'  => $this->t('West'),
                'north' => $this->t('North'),
                'south' => $this->t('South'),
                'ne'    => $this->t('North East'),
                'nw'    => $this->t('North West'),
                'se'    => $this->t('South East'),
                'sw'    => $this->t('South West'),
            ],
        ];
// Agricultural Land Details ...................

        $form['landArea'] = [
            '#type'       => 'number',
            '#attributes' => ['placeholder' => 'Enter area'],
        ];

        $form['landUnit'] = [
            '#type'    => 'select',
            '#options' => [
                'acre'    => $this->t('Acre'),
                'bigha'   => $this->t('Bigha'),
                'hectare' => $this->t('Hectare'),
                'sqft'    => $this->t('Sq Ft'),
            ],
        ];

        $form['landType'] = [
            '#type'    => 'select',
            '#options' => [
                'agricultural' => $this->t('Agricultural'),
                'farm_land'    => $this->t('Farm Land'),
                'orchard'      => $this->t('Orchard'),
                'plantation'   => $this->t('Plantation'),
            ],
        ];

        $form['waterSource'] = [
            '#type'    => 'select',
            '#options' => [
                'canal'     => $this->t('Canal'),
                'tube_well' => $this->t('Tube Well'),
                'river'     => $this->t('River'),
                'pond'      => $this->t('Pond'),
            ],
        ];

        $form['electricityAvailable'] = [
            '#type'    => 'select',
            '#options' => [
                0 => $this->t('No'),
                1 => $this->t('Yes'),
            ],
        ];

        $form['roadAccess'] = [
            '#type'    => 'select',
            '#options' => [
                0 => $this->t('No'),
                1 => $this->t('Yes'),
            ],
        ];

        $form['soilType'] = [
            '#type'       => 'textfield',
            '#attributes' => ['placeholder' => 'e.g. Black, Alluvial'],
        ];

        $form['cropType'] = [
            '#type'       => 'textfield',
            '#attributes' => ['placeholder' => 'e.g. Wheat, Rice'],
        ];
        // Commercial Property Details ......................

        $form['propertyUse'] = [
            '#type'    => 'select',
            '#options' => [
                'office'    => $this->t('Office'),
                'shop'      => $this->t('Shop'),
                'warehouse' => $this->t('Warehouse'),
                'factory'   => $this->t('Factory'),
                'showroom'  => $this->t('Showroom'),
            ],
        ];

        $form['parkingCapacity'] = [
            '#type'       => 'number',
            '#attributes' => ['placeholder' => 'Number of vehicles'],
        ];

        $form['loadingArea'] = [
            '#type' => 'number',
        ];

        $form['storageArea'] = [
            '#type' => 'number',
        ];

        $form['powerBackup'] = [
            '#type'    => 'select',
            '#options' => [
                0 => $this->t('No'),
                1 => $this->t('Yes'),
            ],
        ];

        $form['field_cm_floor_number'] = [
            '#type' => 'number',
        ];

        $form['field_commercial_total_floors'] = [
            '#type' => 'number',
        ];
        //Price Details ................

        $form['expectedPrice'] = [
            '#type'       => 'number',
            '#required'   => true,
            '#attributes' => ['id' => 'expectedPrice'],
        ];

        $form['priceNegotiable'] = [
            '#type'    => 'select',
            '#options' => [
                0 => $this->t('No'),
                1 => $this->t('Yes'),
            ],
        ];

// Rent/Lease Only Fields
        $form['monthlyRent'] = [
            '#type' => 'number',
        ];

        $form['securityDeposit'] = [
            '#type' => 'number',
        ];

// Sale Only Fields
        $form['bookingAmount'] = [
            '#type' => 'number',
        ];

        $form['tokenAmount'] = [
            '#type' => 'number',
        ];

        $form['maintenanceCharges'] = [
            '#type' => 'number',
        ];

        $form['annualPropertyTax'] = [
            '#type' => 'number',
        ];
        $form['amenities'] = [
            '#type'    => 'checkboxes',
            '#options' => [
                'parking'         => $this->t('Parking'),
                'lift'            => $this->t('Lift'),
                'power_backup'    => $this->t('Power Backup'),
                'swimming_pool'   => $this->t('Swimming Pool'),
                'gym'             => $this->t('Gym'),
                'garden'          => $this->t('Garden'),
                'club_house'      => $this->t('Club House'),
                'security'        => $this->t('Security'),
                'cctv'            => $this->t('CCTV'),
                'play_area'       => $this->t('Play Area'),
                'internet'        => $this->t('Internet'),
                'water_supply'    => $this->t('Water Supply'),
                'gas_pipeline'    => $this->t('Gas Pipeline'),
                'rain_harvesting' => $this->t('Rain Harvesting'),
                'solar_system'    => $this->t('Solar System'),
            ],
        ];
        $form['ownershipType'] = [
            '#type'    => 'select',
            '#options' => [
                'freehold'          => $this->t('Freehold'),
                'leasehold'         => $this->t('Leasehold'),
                'power_of_attorney' => $this->t('Power of Attorney'),
            ],
        ];

        $form['ownerName'] = [
            '#type'     => 'textfield',
            '#required' => true,
        ];

        $form['propertyIdNumber'] = [
            '#type' => 'textfield',
        ];

        $form['surveyNumber'] = [
            '#type' => 'textfield',
        ];

        $form['khasraNumber'] = [
            '#type' => 'textfield',
        ];

        $form['khataNumber'] = [
            '#type' => 'textfield',
        ];

        $form['registryAvailable'] = [
            '#type'    => 'select',
            '#options' => [
                0 => $this->t('No'),
                1 => $this->t('Yes'),
            ],
        ];

        $form['mutationAvailable'] = [
            '#type'    => 'select',
            '#options' => [
                0 => $this->t('No'),
                1 => $this->t('Yes'),
            ],
        ];

        $form['approvedMap'] = [
            '#type'    => 'select',
            '#options' => [
                'no'  => $this->t('No'),
                'yes' => $this->t('Yes'),
            ],
        ];

        $form['reraRegistered'] = [
            '#type'    => 'select',
            '#options' => [
                'no'  => $this->t('No'),
                'yes' => $this->t('Yes'),
            ],
        ];

        $form['propertyDispute'] = [
            '#type'    => 'select',
            '#options' => [
                'no'  => $this->t('No'),
                'yes' => $this->t('Yes'),
            ],
        ];

        $form['loanAvailable'] = [
            '#type'    => 'select',
            '#options' => [
                0 => $this->t('No'),
                1 => $this->t('Yes'),
            ],
        ];
        $form['featuredImage'] = [
            '#type'              => 'managed_file',
            '#title'             => $this->t('Featured Image'),
            '#upload_location'   => 'public://property/featured/',
            '#required'          => true,
            '#upload_validators' => [
                'file_validate_extensions' => ['png jpg jpeg'],
            ],
        ];

        $form['propertyGallery'] = [
            '#type'            => 'managed_file',
            '#title'           => $this->t('Property Gallery'),
            '#upload_location' => 'public://property/gallery/',
            '#multiple'        => true,
        ];

        $form['propertyVideo'] = [
            '#type'       => 'url',
            '#attributes' => ['placeholder' => 'YouTube / Vimeo Link'],
        ];

        $form['documentsUpload'] = [
            '#type'            => 'managed_file',
            '#title'           => $this->t('Documents Upload'),
            '#upload_location' => 'public://property/documents/',
            '#multiple'        => true,
        ];
        $form['contactName'] = [
            '#type'     => 'textfield',
            '#required' => true,
        ];

        $form['contactNumber'] = [
            '#type'     => 'tel',
            '#required' => true,
        ];

        $form['whatsappNumber'] = [
            '#type' => 'tel',
        ];

        $form['emailAddress'] = [
            '#type' => 'email',
        ];

        $form['bestTimeToContact'] = [
            '#type'    => 'select',
            '#options' => [
                'anytime'            => $this->t('Anytime'),
                'morning_9am_12pm'   => $this->t('Morning (9AM - 12PM)'),
                'afternoon_12pm_5pm' => $this->t('Afternoon (12PM - 5PM)'),
                'evening_5pm_9pm'    => $this->t('Evening (5PM - 9PM)'),
            ],
        ];
        // Submit Button
        $form['submit'] = [
            '#type'  => 'submit',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }
    public function updatePropertyTypes(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRebuild(true);
        // Return only wrapper so Drupal AJAX can replace it.
        return $form['propTypeWrapper'];
    }

    public function nextStepSubmit(array &$form, FormStateInterface $form_state)
    {

        $step = $form_state->get('step') ?: 1;

        // Save step values.
        $stored  = $form_state->get('property_data') ?: [];
        $stored += $form_state->getValues();

        $form_state->set('property_data', $stored);
        $form_state->set('step', $step + 1);

        $form_state->setRebuild(true);
    }

    public function previousStepSubmit(array &$form, FormStateInterface $form_state)
    {

        $step = $form_state->get('step');
        $form_state->set('step', $step - 1);

        $form_state->setRebuild(true);
    }
    private function getPropertyCategories()
    {
        $options = [
            '' => $this->t('Select Category'),
        ];

        $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadTree('properties', 0, 1);

        foreach ($terms as $term) {
            $options[$term->tid] = $term->name;
        }

        return $options;
    }
    private function getPropertyTypes($parent_tid = null)
    {

        // Return only real child options. The UI empty label is handled by '#empty_option'.
        $options = [];
        if (! $parent_tid) {
            return $options;
        }

        $children = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadTree('properties', $parent_tid, 1);

        foreach ($children as $child) {
            $options[$child->tid] = $child->name;
        }
        return $options;
    }
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $required_fields = [
            'propTitle'     => $this->t('Property Title is required.'),
            'propPurpose'   => $this->t('Property Purpose is required.'),
            'propCategory'  => $this->t('Property Category is required.'),
            'propType'      => $this->t('Property Type is required.'),
            'state'         => $this->t('State is required.'),
            'city'          => $this->t('City is required.'),
            'locality'      => $this->t('Locality is required.'),
            'pincode'       => $this->t('Pincode is required.'),
            'areaUnit'      => $this->t('Area Unit is required.'),
            'totalArea'     => $this->t('Total Area is required.'),
            'expectedPrice' => $this->t('Expected Price is required.'),
            'ownerName'     => $this->t('Owner Name is required.'),
            'featuredImage' => $this->t('Featured Image is required.'),
            'contactName'   => $this->t('Contact Name is required.'),
            'contactNumber' => $this->t('Contact Number is required.'),
        ];

        foreach ($required_fields as $field_name => $message) {
            $value = $form_state->getValue($field_name);
            if ($value === null || $value === '' || $value === []) {
                $form_state->setErrorByName($field_name, $message);
            }
        }

        if ($form_state->getValue('propCategory') === '44') {
            $land_area = $form_state->getValue('landArea');
            if ($land_area === null || $land_area === '') {
                $form_state->setErrorByName('landArea', $this->t('Land Area is required.'));
            }
        }

        $pincode = trim((string) $form_state->getValue('pincode'));
        if ($pincode !== '' && ! preg_match('/^\d{6}$/', $pincode)) {
            $form_state->setErrorByName('pincode', $this->t('Please enter a valid 6 digit pincode.'));
        }

        foreach (['contactNumber' => $this->t('Contact Number'), 'whatsappNumber' => $this->t('WhatsApp Number')] as $field_name => $label) {
            $phone = trim((string) $form_state->getValue($field_name));
            if ($phone !== '' && ! preg_match('/^[0-9+\-\s()]{10,20}$/', $phone)) {
                $form_state->setErrorByName($field_name, $this->t('@label is not valid.', ['@label' => $label]));
            }
        }

        foreach (['totalArea', 'expectedPrice'] as $field_name) {
            $value = $form_state->getValue($field_name);
            if ($value !== null && $value !== '' && (! is_numeric($value) || $value <= 0)) {
                $form_state->setErrorByName($field_name, $this->t('Please enter a value greater than 0.'));
            }
        }

        $land_area = $form_state->getValue('landArea');
        if ($land_area !== null && $land_area !== '' && (! is_numeric($land_area) || $land_area <= 0)) {
            $form_state->setErrorByName('landArea', $this->t('Please enter a value greater than 0.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {

        $values = $form_state->getValues();
        // Create node
        $node = Node::create([
            'type'                        => 'properties_listing_form',

            'title'                       => $values['propTitle'] ?? '',

            'field_purpose_of'            => $values['propPurpose'] ?? null,
            'body'                        => $values['propDescription'] ?? null,

            'field_property_type'         => $values['propType'] ?? null,

            'field_property_highlights'   => $values['propHighlights'] ?? '',

            'field_country'               => $values['country'] ?? '',
            'field_state'                 => $values['state'] ?? '',
            'field_district'              => $values['district'] ?? '',
            'field_city'                  => $values['city'] ?? '',
            'field_locality'              => $values['locality'] ?? '',
            'field_village'               => $values['village'] ?? '',
            'field_landmark'              => $values['landmark'] ?? '',
            'field_pincode'               => $values['pincode'] ?? '',
            'field_complete_address'      => $values['address'] ?? '',

            'field_latitude'              => $values['latitude'] ?? '',
            'field_longitude'             => $values['longitude'] ?? '',

            'field_area_unit'             => $values['areaUnit'] ?? '',
            'field_total_area'            => $values['totalArea'] ?? '',
            'field_built_up_area'         => $values['builtupArea'] ?? '',
            'field_carpet_area'           => $values['carpetArea'] ?? '',
            'field_super_area'            => $values['superArea'] ?? '',

            'field_front_width_ft'        => $values['frontWidth'] ?? '',
            'field_road_width_ft'         => $values['roadWidth'] ?? '',
            'field_length_ft'             => $values['length'] ?? '',
            'field_width_ft'              => $values['width'] ?? '',

            'field_corner_property'       => $values['cornerProperty'] ?? '',
            'field_bhk_type'              => $values['bhkType'] ?? '',
            'field_bedrooms'              => $values['bedrooms'] ?? 0,
            'field_bathrooms'             => $values['bathrooms'] ?? 0,
            'field_balconies'             => $values['balconies'] ?? 0,

            'field_kitchen'               => $values['kitchen'] ?? 0,
            'field_drawing_room'          => $values['drawingRoom'] ?? 0,
            'field_dining_room'           => $values['diningRoom'] ?? 0,
            'field_store_room'            => $values['storeRoom'] ?? 0,

            'field_floor_number'          => $values['floorNumber'] ?? 0,
            'field_total_floors'          => $values['totalFloors'] ?? 0,

            'field_property_facing'       => $values['propertyFacing'] ?? '',

            'field_ag_land_area'          => $values['landArea'] ?? '',
            'field_ag_land_unit'          => $values['landUnit'] ?? '',
            'field_ag_land_type'          => $values['landType'] ?? '',

            'field_water_source'          => $values['waterSource'] ?? '',
            'field_electricity_available' => $values['electricityAvailable'] ? 1 : 0,
            'field_road_access'           => $values['roadAccess'] ?? 0,

            'field_soil_type'             => $values['soilType'] ?? '',
            'field_crop_type'             => $values['cropType'] ?? '',

            'field_property_use'          => $values['propertyUse'] ?? '',
            'field_parking_capacity'      => $values['parkingCapacity'] ?? 0,
            'field_loading_area_sq_ft'    => $values['loadingArea'] ?? 0,
            'field_storage_area_sq_ft'    => $values['storageArea'] ?? 0,

            'field_power_backup'          => $values['powerBackup'] ?? 0,
            'field_cm_floor_number'       => $values['field_cm_floor_number'] ?? 0,

            'field_expected_price'        => $values['expectedPrice'] ?? '',
            'field_price_negotiable'      => $values['priceNegotiable'] ?? 0,

            'field_monthly_rent'          => $values['monthlyRent'] ?? '',
            'field_security_deposit'      => $values['securityDeposit'] ?? '',

            'field_booking_amount'        => $values['bookingAmount'] ?? '',
            'field_token_amount'          => $values['tokenAmount'] ?? '',
            'field_ownership_type'        => $values['ownershipType'] ?? '',
            'field_maintenance_charges'   => $values['maintenanceCharges'] ?? '',
            'field_annual_property_tax'   => $values['annualPropertyTax'] ?? '',

            'field_owner_name'            => $values['ownerName'] ?? '',
            'field_contact_name'          => $values['contactName'] ?? '',
            'field_contact_number'        => $values['contactNumber'] ?? '',
            'field_whatsapp_number'       => $values['whatsappNumber'] ?? '',
            'field_email_address'         => $values['emailAddress'] ?? '',

            'field_best_time_to_contact'  => $values['bestTimeToContact'] ?? '',

            'field_property_id_number'    => $values['propertyIdNumber'] ?? '',
            'field_survey_number'         => $values['surveyNumber'] ?? '',
            'field_khasra_number'         => $values['khasraNumber'] ?? '',
            'field_khata_number'          => $values['khataNumber'] ?? '',

            'field_registry_available'    => $values['registryAvailable'] ?? 0,
            'field_mutation_available'    => $values['mutationAvailable'] ?? 0,
            'field_approved_map'          => $values['approvedMap'] ?? 0,
            'field_rera_registered'       => $values['reraRegistered'] ?? 0,
            'field_loan_available'        => $values['loanAvailable'] ?? 0,
            'field_property_dispute'      => $values['propertyDispute'] ?? 0,
            'field_pooja_room'            => $values['poojaRoom'] ?? 0,
            'field_study_room'            => $values['studyRoom'] ?? 0,
            'uid' => \Drupal::currentUser()->id(),
            'status' => 0, // Unpublished
        ]);
        /** -----------------------------
         * HANDLE FEATURED IMAGE
         * ----------------------------- */
        $fid = $values['featuredImage'] ?? [];

        if (! empty($fid)) {
            // managed_file returns array of fids
            $file = File::load(reset($fid));

            if ($file) {
                // Make file permanent (important for production)
                $file->setPermanent();
                $file->save();

                // Attach to node image field
                $node->set('field_featured_image', [
                    'target_id' => $file->id(),
                    'alt'       => $values['propTitle'] ?? '',
                ]);
            }
        }
        /** -----------------------------
         * HANDLE GALLERY IMAGES
         * ----------------------------- */
        $gallery_fids = $values['propertyGallery'] ?? [];

        $gallery_items = [];

        if (! empty($gallery_fids)) {

            foreach ($gallery_fids as $fid) {

                $file = File::load($fid);

                if ($file) {
                    // Make permanent
                    $file->setPermanent();
                    $file->save();

                    // Prepare field item
                    $gallery_items[] = [
                        'target_id' => $file->id(),
                        'alt'       => $values['propTitle'] ?? '',
                    ];
                }
            }
        }

        // Set multiple image field
        if (! empty($gallery_items)) {
            $node->set('field_property_gallery', $gallery_items);
        }

        /** -----------------------------
         * HANDLE DOCUMENT UPLOADS
         * ----------------------------- */
        $doc_fids = $values['documentsUpload'] ?? [];

        $doc_items = [];

        if (! empty($doc_fids)) {

            foreach ($doc_fids as $fid) {

                $file = File::load($fid);

                if ($file) {

                    // Make file permanent (important in production)
                    $file->setPermanent();
                    $file->save();

                    // Attach to node file field
                    $doc_items[] = [
                        'target_id' => $file->id(),
                    ];
                }
            }
        }

        if (! empty($doc_items)) {
            $node->set('field_upload_documents', $doc_items);
        }

        $node->save();

        $file_usage = \Drupal::service('file.usage');
        foreach (array_filter(array_merge(
            (array) ($values['featuredImage'] ?? []),
            (array) ($values['propertyGallery'] ?? []),
            (array) ($values['documentsUpload'] ?? [])
        )) as $fid) {
            if ($file = File::load($fid)) {
                $file_usage->add($file, 'partner_dashboard', 'node', $node->id());
            }
        }

        $this->messenger()->addStatus($this->t('Property saved successfully!'));
        // Redirect to payment page with node id
        $form_state->setRedirect(
            'mangalpath_payment.payment',
            ['node' => $node->id()]
        );
        }
        catch (\Throwable $e) {
            \Drupal::logger('partner_dashboard')->error('Property form submit failed: @message', [
                '@message' => $e->getMessage(),
            ]);
            $this->messenger()->addError($this->t('Property could not be submitted. Please check the form and try again. Error: @message', [
                '@message' => $e->getMessage(),
            ]));
            $form_state->setRebuild(true);
        }
    }
}
