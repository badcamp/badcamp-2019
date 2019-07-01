<?php

namespace Drupal\badcamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\currency\Plugin\Validation\Constraint\CurrencyCode;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;

/**
 * Class PaymentController.
 */
class PaymentController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentStorage;

  /**
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface
   */
  protected $paymentLineItemManager;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * PaymentController constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $paymentStorage
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface $paymentLineItemManager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entityFormBuilder
   */
  public function __construct( EntityStorageInterface $paymentStorage, PaymentLineItemManagerInterface $paymentLineItemManager,  RequestStack $requestStack, EntityFormBuilderInterface $entityFormBuilder) {
    $this->paymentStorage = $paymentStorage;
    $this->paymentLineItemManager = $paymentLineItemManager;
    $this->requestStack = $requestStack;
    $this->entityFormBuilder = $entityFormBuilder;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('payment'),
      $container->get('plugin.manager.payment.line_item'),
      $container->get('request_stack'),
      $container->get('entity.form_builder')
    );
  }

  /**
   *
   */
  public function payment($type) {
    $amount = isset($_GET['amount']) ? $_GET['amount'] : 25;
    $type = ($type != 'badcamp_payment_sponsorship' && $type != 'badcamp_payment_organization_sponsorship') ? 'badcamp_payment_sponsorship' : $type;

    $line_title = $this->t('BADCamp Individual Sponsorship');
    if ($type == 'badcamp_payment_sponsorship' && ($amount != 25 && $amount != 50 && $amount != 100) ){
      $amount = 25;
    }
    elseif ($type == 'badcamp_payment_organization_sponsorship') {
      $amount = 400;
      $line_title = $this->t('BADCAMP Organization Sponsorship');
    }

    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->paymentStorage->create([
      'bundle' => $type,
    ])
    ->setCurrencyCode('USD');

    $line_item = $this->paymentLineItemManager
      ->createInstance('payment_basic')
      ->setDescription($line_title)
      ->setQuantity(1)
      ->setAmount($amount)
      ->setCurrencyCode('USD');

    $payment
      ->setLineItem($line_item);

    $payment->redirectDestination = '/';

    $form = $this->entityFormBuilder->getForm($payment, 'payment_form');

    return [
      'form' => $form,
    ];
  }
}
