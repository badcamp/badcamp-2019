<?php

namespace Drupal\badcamp\EventSubscriber;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hook_event_dispatcher\Event\Entity\EntityAccessEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;

/**
 * Class SponsorSubscriber
 *
 * @package Drupal\badcamp\EventSubscriber
 */
class SponsorSubscriber implements EventSubscriberInterface {

  /**
   * The Account Proxy Service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $accountProxy;

  /**
   * SponsorSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $accountProxy
   */
  public function __construct(AccountProxyInterface $accountProxy) {
    $this->accountProxy = $accountProxy;
  }

  /**
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityAccessEvent $accessEvent
   *   The Entity Access Event.
   *
   * @return \Drupal\Core\Access\AccessResultAllowed
   */
  public function entityAccess(EntityAccessEvent $accessEvent) {
    $entity = $accessEvent->getEntity();
    if ($entity->getEntityTypeId() == 'node' && in_array($entity->bundle(), ['sponsor', 'event', 'summit', 'sessions', 'training']) && $accessEvent->getOperation() == 'update') {

      // Get the Author of the Node
      $author = $entity->getOwnerId();
      // Get the Members of the Node
      $members = $entity->get('field_members')->getValue();

      // Add Author to the List
      $members[] = [
        'target_id' => $author,
      ];

      // Loop through the people
      foreach ($members AS $member) {
        // If current user is in the list give access to update.
        if ($member['target_id'] == $this->accountProxy->id()) {
          $accessEvent->setAccessResult(AccessResult::allowed());
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[HookEventDispatcherInterface::ENTITY_ACCESS] = ['entityAccess'];
    return $events;
  }
}
