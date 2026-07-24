<?php

namespace Drupal\mangalpath_module\EventSubscriber;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AnonymousRedirectSubscriber implements EventSubscriberInterface {

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * Redirect anonymous users.
   */
  public function onRequest(RequestEvent $event) {

    if (!$event->isMainRequest()) {
      return;
    }

    // Logged in users are always allowed.
    if ($this->currentUser->isAuthenticated()) {
      return;
    }

    $request = $event->getRequest();

    // Skip non-HTML requests (AJAX, JSON, etc.).
    $format = $request->getRequestFormat();
    if ($format !== 'html') {
      return;
    }

    $path = $request->getPathInfo();

    // Allow static assets.
    $static_prefixes = [
      '/core/',
      '/themes/',
      '/modules/',
      '/libraries/',
      '/sites/',
    ];

    foreach ($static_prefixes as $prefix) {
      if (str_starts_with($path, $prefix)) {
        return;
      }
    }

    // Allow these public pages.
    $allowed_paths = [
      '/',
      '/user/login',
      '/user/password',
      '/user/register',
    ];

    if (in_array($path, $allowed_paths, TRUE)) {
      return;
    }

    // Redirect to login.
    $login_url = Url::fromRoute('user.login', [], [
      'query' => [
        'destination' => $path,
      ],
    ]);

    $event->setResponse(
      new RedirectResponse($login_url->toString())
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onRequest', 30],
    ];
  }

}