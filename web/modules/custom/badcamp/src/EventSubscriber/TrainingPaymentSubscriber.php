<?php

namespace Drupal\badcamp\EventSubscriber;

/**
 * @file
 * Contains \Drupal\badcamp\EventSubscriber\TrainingPaymentSubscriber.
 */

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\Language;
use Drupal\Driver\Exception\Exception;
use Drupal\flag\FlagService;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\payment\Event\PaymentEvents;
use Drupal\payment\Event\PaymentStatusSet;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber TrainingPaymentSubscriber.
 */
class TrainingPaymentSubscriber implements EventSubscriberInterface {

  /**
   * The EntityTypeManager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Flag Service.
   *
   * @var \Drupal\flag\FlagService $flagService
   */
  protected $flagService;

  /**
   * Flag To Add to Schedule.
   *
   * @var \Drupal\Core\Entity\EntityInterface|\Drupal\flag\FlagInterface|null $flag
   */
  protected $flag;

  /**
   * TrainingPaymentSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The Entity Type Manager Service.
   * @param \Drupal\flag\FlagService $flagService
   *   The Flag Service.
   */
  public function __construct(EntityTypeManager $entityTypeManager, FlagService $flagService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->flagService = $flagService;
    $this->flag = $flagService->getFlagById('add_to_schedule');
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

    if ($entity->getEntityTypeId() == 'payment' && $entity->bundle() == 'badcamp_payment_training' && !$entity->isNew() && $paymentStatus->getPluginId() == 'payment_success' && $paymentStatusSet->getPreviousPaymentStatus()->getPluginId() !== 'payment_success') {
      $referenceItem =  $entity->get('field_training')->first();
      $entityReference = $referenceItem->get('entity');
      $entityAdapter = $entityReference->getTarget();
      $training = $entityAdapter->getValue();
      try {
        $this->flagService->flag($this->flag, $training);
      }
      catch(Exception $exception){}
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[PaymentEvents::PAYMENT_STATUS_SET] = ['paymentStatusUpdated'];
    return $events;
  }

}
