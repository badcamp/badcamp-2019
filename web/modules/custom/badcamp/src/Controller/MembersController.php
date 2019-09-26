<?php

namespace Drupal\badcamp\Controller;

use Drupal\badcamp\Form\AddUserToEvent as AddUserToEventForm;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\NodeInterface;
use Drupal\views\Views;

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

    $form = $this->formBuilder()->getForm(AddUserToEventForm::class, $node);

    return [
      'view' => $members,
      'form' => $form
    ];
  }

  /**
   * Return the title for the page.
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function title(NodeInterface $node) {
    return $this->t('Attendees for @title', ['@title' => $node->getTitle()]);
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
