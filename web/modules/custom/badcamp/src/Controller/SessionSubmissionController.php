<?php

namespace Drupal\badcamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SessionSubmissionController
 *
 * @package Drupal\badcamp\Controller
 */
class SessionSubmissionController extends ControllerBase{

  /**
   * Checks if user is logged in.
   */
  public function checkUser() {
    $url = '';
    if ($this->currentUser()->isAuthenticated()) {
      $url = '/node/add/session';
    }
    else {
      $url = '/registration';
    }

    return RedirectResponse::create($url);
  }
}
