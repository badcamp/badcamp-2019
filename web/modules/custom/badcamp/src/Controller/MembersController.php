<?php

namespace Drupal\badcamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Plugin\views\filter\Access;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MembersController.
 */
class MembersController extends ControllerBase {

  /**
   * Attendee List for Specific Event.
   *
   * @return string
   *   Return Hello string.
   */
  public function attendeesList(NodeInterface $node) {
    $view = Views::getView('sign_up_list');
    $members = $view->buildRenderable('default');

    return [
      'view' => $members,
    ];
  }

  /**
   * @param \Drupal\node\NodeInterface $node
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return \Drupal\Core\Access\AccessResultAllowed
   */
  public function access(NodeInterface $node, AccountInterface $account) {
    return AccessResult::allowedIf($node->access('update', $account) && in_array($node->bundle(), ['summit', 'training', 'event', 'session']));
  }

}
