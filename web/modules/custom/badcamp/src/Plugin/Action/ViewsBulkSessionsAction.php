<?php

namespace Drupal\badcamp\Plugin\Action;

use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * An action to mas
 *
 * @Action(
 *   id = "session_action",
 *   label = @Translation("Session status nodes"),
 *   deriver = "Drupal\badcamp\Plugin\Derivative\ViewsBulkSessionsActionDeriver",
 *   type = "node"
 * )
 */
class ViewsBulkSessionsAction extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity instanceof NodeInterface && $entity->bundle() == 'session') {
      list(,$action) = explode(':',  $this->getPluginId());
      $entity->set('field_status', $action);
      $entity->save();
    }
    return sprintf('Session set to Proposed: %s', $entity->getTitle());
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    return TRUE;
  }

}
