<?php

namespace Drupal\badcamp\EventSubscriber;

/**
 * @file
 * Contains \Drupal\badcamp\EventSubscriber\OrganizationPaymentSubscriber.
 */

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\Language;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\payment\Event\PaymentEvents;
use Drupal\payment\Event\PaymentStatusSet;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber OrganizationPaymentSubscriber.
 */
class OrganizationPaymentSubscriber implements EventSubscriberInterface {

  /**
   * The EntityTypeManager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * OrganizationPaymentSubscriber constructor.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Run Event on Entity Insert.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The Entity Insert Event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function entityInsert(EntityInsertEvent $event) {
    /** @var \Drupal\payment\Entity\Payment $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() == 'payment' && $entity->bundle() == 'badcamp_payment_organization_sponsorship') {
      $nodeDefinition = $this->entityTypeManager->getDefinition('node');

      $data = [
        $nodeDefinition->getKey('bundle') => 'sponsor',
        'langcode' => Language::LANGCODE_NOT_SPECIFIED,
        'title' => $entity->get('field_organization_name')->getString(),
        'field_sponsor_level' => [$this->entityTypeManager->getStorage('taxonomy_term')->load(3)],
        'field_members' => [$entity->getOwner()],
      ];

      if (!$entity->get('field_organization_url')->isEmpty()) {
        $data['field_url'] = $entity->get('field_organization_url')->getValue();
      }

      if (!$entity->get('field_organization_description')->isEmpty()) {
        $data['field_sponsor_description'] = $entity->get('field_organization_description')->getValue();
      }

      if (!$entity->get('field_organization_logo')->isEmpty()) {
        $data['field_sponsor_logo'] = $entity->get('field_organization_logo')->getValue();
      }

      $node = $this->entityTypeManager->getStorage('node')->create($data);
      $node->save();
    }
  }

  /**
   * Run update on payment status update.
   *
   * @param \Drupal\payment\Event\PaymentStatusSet $paymentStatusSet
   *   The payment status entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function paymentStatusUpdated(PaymentStatusSet $paymentStatusSet) {
    /** @var \Drupal\payment\Entity\Payment $entity */
    $entity = $paymentStatusSet->getPayment();
    /** @var \Drupal\payment\Entity\PaymentStatus $status */
    $paymentStatus = $entity->getPaymentStatus();

    if ($entity->getEntityTypeId() == 'payment' && $entity->bundle() == 'badcamp_payment_organization_sponsorship' && !$entity->isNew() && $paymentStatus->getPluginId() == 'payment_success' && $paymentStatusSet->getPreviousPaymentStatus()->getPluginId() !== 'payment_success') {
      $nodeDefinition = $this->entityTypeManager->getDefinition('node');

      $data = [
        $nodeDefinition->getKey('bundle') => 'sponsor',
        'langcode' => Language::LANGCODE_NOT_SPECIFIED,
        'title' => $entity->get('field_organization_name')->getString(),
        'field_sponsor_level' => [$this->entityTypeManager->getStorage('taxonomy_term')->load(3)],
        'field_members' => [$entity->getOwner()],
      ];

      if (!$entity->get('field_organization_url')->isEmpty()) {
        $data['field_url'] = $entity->get('field_organization_url')->getValue();
      }

      if (!$entity->get('field_organization_description')->isEmpty()) {
        $data['field_sponsor_description'] = $entity->get('field_organization_description')->getValue();
      }

      if (!$entity->get('field_organization_logo')->isEmpty()) {
        $data['field_sponsor_logo'] = $entity->get('field_organization_logo')->getValue();
      }

      $node = $this->entityTypeManager->getStorage('node')->create($data);
      $node->save();

      $entity->set('field_sponsor_node', $node);
      $entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    //$events[HookEventDispatcherInterface::ENTITY_INSERT] = ['entityInsert'];
    $events[PaymentEvents::PAYMENT_STATUS_SET] = ['paymentStatusUpdated'];
    return $events;
  }

}