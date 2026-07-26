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

  // Logged-in users are always allowed.
  if ($this->currentUser->isAuthenticated()) {
    return;
  }

  $request = $event->getRequest();
  $path = $request->getPathInfo();

  /**
   * -------------------------------------------------------
   * Allow PWA files
   * -------------------------------------------------------
   */
  if (
    $path === '/manifest.json' ||
    $path === '/manifest.webmanifest' ||
    $path === '/service-worker.js' ||
    $path === '/sw.js' ||
    str_ends_with($path, '.webmanifest')
  ) {
    return;
  }

  /**
   * -------------------------------------------------------
   * Allow static assets
   * -------------------------------------------------------
   */
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

  /**
   * -------------------------------------------------------
   * Skip AJAX / JSON requests
   * -------------------------------------------------------
   */
  if (
    $request->isXmlHttpRequest() ||
    str_contains($request->headers->get('Accept', ''), 'application/json')
  ) {
    return;
  }

  /**
   * -------------------------------------------------------
   * Allow public pages
   * -------------------------------------------------------
   */
  $allowed_paths = [
    '/',
    '/user/login',
    '/user/password',
    '/user/register',
    '/user/register/otp',
    '/login-otp',
  ];

  if (
    in_array($path, $allowed_paths, TRUE) ||
    str_starts_with($path, '/user/')
  ) {
    return;
  }

  /**
   * -------------------------------------------------------
   * Redirect anonymous users
   * -------------------------------------------------------
   */
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