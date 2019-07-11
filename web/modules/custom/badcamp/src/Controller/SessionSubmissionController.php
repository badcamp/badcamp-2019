<?php

namespace Drupal\badcamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SessionSubmissionController
 *
 * @package Drupal\badcamp\Controller
 */
class SessionSubmissionController extends ControllerBase{

  /**
   * The Account Service.
   *
   * @var \Drupal\Core\Session\AccountInterface $account
   */
  protected $account;

  /**
   * SessionSubmissionController constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface
   *   The Account Service.
   */
  public function __construct(AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * Checks if user is logged in.
   */
  public function checkUser() {
    $url = '';
    if ($this->currentUser->isAuthenticated()) {
      $url = '/node/add/session';
    }
    else {
      $url = '/registration';
    }

    return RedirectResponse::create($url);
  }
}
