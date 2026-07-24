<?php

namespace Drupal\mangalpath_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mangalpath_module\Service\DashboardStatisticsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Partner Dashboard Controller.
 */
class PartnerDashboardController extends ControllerBase {

  protected DashboardStatisticsService $dashboardStatistics;

  public function __construct(DashboardStatisticsService $dashboard_statistics) {
    $this->dashboardStatistics = $dashboard_statistics;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('mangalpath_module.dashboard_statistics')
    );
  }

  public function dashboard(): array|RedirectResponse {
    $roles = $this->currentUser()->getRoles();

    if (
      !in_array('administrator', $roles, TRUE) &&
      !in_array('partner', $roles, TRUE)
    ) {
      return $this->redirect('<front>');
    }

    $statistics = $this->dashboardStatistics->getStatistics();
    $user =$this->currentUser();
    

    return [
      '#theme' => 'partner_dashboard',
      '#statistics' => $statistics,
      '#user' => $user,
      '#cache' => [
        'contexts' => ['user', 'user.roles'],
        'tags' => ['node_list'],
      ],
    ];
  }
}
