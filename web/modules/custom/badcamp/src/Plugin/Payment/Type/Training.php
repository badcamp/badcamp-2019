<?php

namespace Drupal\badcamp\Plugin\Payment\Type;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\TranslationWrapper;
use Drupal\payment\EventDispatcherInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A training payment type.
 *
 * @PaymentType(
 *   id = "badcamp_payment_training",
 *   label = @Translation("Training")
 * )
 */
class Training extends PaymentTypeBase {

  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed[] $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\payment\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EventDispatcherInterface $event_dispatcher, TranslationInterface $string_translation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher);
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('payment.event_dispatcher'), $container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  public function resumeContextAccess(AccountInterface $account) {
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  protected function doGetResumeContextResponse() {
    throw new NotFoundHttpException();
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentDescription() {
    return new TranslationWrapper('Training');
  }

}
