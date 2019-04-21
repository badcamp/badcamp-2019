<?php

namespace Drupal\badcamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\NodeInterface;

/**
 * Class MembersController.
 */
class MembersController extends ControllerBase {

  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function attendeesList(NodeInterface $node) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: hello with parameter(s): $node'),
    ];
  }

  /**
   * @param \Drupal\node\NodeInterface $node
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return \Drupal\Core\Access\AccessResultAllowed
   */
  public function access(NodeInterface $node, AccountInterface $account) {
    return AccessResult::allowed();
  }

}
