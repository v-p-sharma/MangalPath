<?php
namespace Drupal\mangalpath_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

class LoanController extends ControllerBase
{

    public function loanListing()
    {

        $header = [
            'ID',
            'Name',
            'Father',
            'Mother',
            'DOB',
            'Gender',
            'Phone',
            'Email',
            'Caste',
            'Employee',
            'Loan Type',
            'Required Amount',
            'Annual Income',
            'Running Loan',
            'Loan Amount',
            'Current Address',
            'Permanent Address',
            'Action',
        ];

        $rows = [];

        $nids = \Drupal::entityQuery('node')
            ->condition('type', 'loan_section')
            ->accessCheck(false)
            ->execute();

        $nodes = Node::loadMultiple($nids);

        foreach ($nodes as $node) {

            $loan_type = '';

            if (! $node->get('field_loan_type')->isEmpty()) {
                $loan_type = $node->get('field_loan_type')->entity->label();
            }

            $rows[] = [

                $node->id(),

                $node->get('field_first_name')->value . ' ' .
                $node->get('field_last_name')->value,

                $node->get('field_father_s_name')->value,

                $node->get('field_mother_s_name')->value,

                $node->get('field_date_of_birth')->value,

                $node->get('field_gender')->value,

                $node->get('field_phone_number')->value,

                $node->get('field_loan_email_address')->value,

                $node->get('field_caste_category')->value,

                $node->get('field_employee_type')->value,

                $loan_type,

                '₹ ' . $node->get('field_required_amount')->value,

                '₹ ' . $node->get('field_annual_income')->value,

                $node->get('field_current_running_loan')->value,

                '₹ ' . $node->get('field_if_yes_loan_amount')->value,

                $node->get('field_current_address')->value,

                $node->get('field_permanent_address')->value,

                Link::fromTextAndUrl(
                    'View',
                    Url::fromRoute('entity.node.canonical', ['node' => $node->id()])
                ),

            ];
        }
        $build['wrapper'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => ['table-responsive container-fluid'],
            ],
        ];

        $build['wrapper']['table'] = [
            '#type'       => 'table',
            '#header'     => $header,
            '#rows'       => $rows,
            '#empty'      => $this->t('No loan applications found.'),
            '#attributes' => [
                'class' => ['table table-bordered table-striped'],
            ],
              '#cache' => [
    'max-age' => 0,
  ],

        ];

        return $build;

    }

    /**
     * Partner subscription report.
     */
    public function partnerSubscriptionReport() {

    $database = Database::getConnection();

    $header = [
        $this->t('S.No'),
        $this->t('Partner Name'),
        $this->t('Email'),
        $this->t('Total Plans'),
        $this->t('Subscription Amount'),
        $this->t('Last Payment'),
        $this->t('Action'),
    ];

    // Fetch ALL Partner users.
    $query = $database->select('users_field_data', 'u');

    $query->join('user__roles', 'r', 'r.entity_id = u.uid');

    // LEFT JOIN so partners without transactions are also shown.
    $query->leftJoin(
        'mangalpath_payment_transaction',
        't',
        "t.uid = u.uid AND t.status = 'success'"
    );

    $query->fields('u', [
        'uid',
        'name',
        'mail',
    ]);

    $query->condition('u.status', 1);
    $query->condition('r.roles_target_id', 'partner');

    $query->addExpression('COUNT(t.id)', 'total_plans');
    $query->addExpression('COALESCE(SUM(t.amount), 0)', 'total_amount');
    $query->addExpression('MAX(t.created)', 'last_payment');

    $query->groupBy('u.uid');
    $query->groupBy('u.name');
    $query->groupBy('u.mail');

    $query->orderBy('u.name', 'ASC');

    $result = $query->execute();

    $rows = [];
    $i = 1;

    foreach ($result as $record) {

        $rows[] = [
            $i++,
            $record->name,
            $record->mail,
            $record->total_plans,
            '₹ ' . number_format($record->total_amount, 2),
            !empty($record->last_payment)
                ? date('d M Y h:i A', $record->last_payment)
                : '-',
            Link::fromTextAndUrl(
                $this->t('View'),
                Url::fromRoute('entity.user.canonical', [
                    'user' => $record->uid,
                ])
            ),
        ];

    }

    // Total Revenue.
    $query = $database->select('mangalpath_payment_transaction', 't');
    $query->condition('status', 'success');
    $query->addExpression('COALESCE(SUM(amount),0)', 'revenue');
    $total_revenue = $query->execute()->fetchField();

    // Total Successful Transactions.
    $query = $database->select('mangalpath_payment_transaction', 't');
    $query->condition('status', 'success');
    $total_transactions = $query->countQuery()->execute()->fetchField();

    // Total Partners.
    $total_partners = \Drupal::entityQuery('user')
        ->condition('status', 1)
        ->condition('roles', 'partner')
        ->accessCheck(FALSE)
        ->count()
        ->execute();

    $build = [];
    $build['#attached']['library'][] = 'mangalpath_module/notification';

    // Summary Cards.
    $build['summary'] = [
        '#markup' => '
        <div class="container-fluid">
        <div class="row mb-4">

            <div class="col-md-4">
                <div class="alert alert-primary">
                    <h5>Total Partners</h5>
                    <h2>' . $total_partners . '</h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-success">
                    <h5>Total Revenue</h5>
                    <h2>₹ ' . number_format($total_revenue, 2) . '</h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-info">
                    <h5>Successful Transactions</h5>
                    <h2>' . $total_transactions . '</h2>
                </div>
            </div>

        </div> </div>',
    ];

    // Bootstrap Responsive Wrapper.
    $build['table_wrapper_start'] = [
        '#markup' => '<div class="table-responsive container-fluid" style="
    margin: 20px;">',
    ];

    $build['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No partner found.'),
        '#attributes' => [
            'class' => [
                'table',
                'table-bordered',
                'table-striped',
                'table-hover',
            ],
        ],
          '#cache' => [
    'max-age' => 0,
  ],

        '#responsive' => FALSE,
    ];

    $build['table_wrapper_end'] = [
        '#markup' => '</div>',
    ];

    return $build;

}

}
