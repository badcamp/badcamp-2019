<?php

namespace Drupal\badcamp\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\payment\Entity\Payment;
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
   * @param $type
   */
  private function getPaymentInfo($type, $default = FALSE) {
    $payments = $this->config('badcamp.settings')->get('payments');
    return isset($payments[$type]) ? $payments[$type] : $payments[$default];
//    return [
//      'page_title' => 'test',
//      'amount' => 500,
//      'title' => 'help'
//    ];
  }

  /**
   * @param $type
   */
  public function title($type) {
    return $this->getPaymentInfo($type)['page_title'];
  }

  /**
   * @param $type
   *
   * @return array
   */
  public function payment($type) {
    $paymentInfo = $this->getPaymentInfo($type, 'badcamp_payment_sponsorship');
    $amount = isset($_GET['amount']) ? $_GET['amount'] : $paymentInfo['amount'];

    $redirectDestination = isset($paymentInfo['redirect']) ? $paymentInfo['redirect'] : '/';

    $line_title = $paymentInfo['title'];
    if (isset($paymentInfo['allowed_amounts']) && !in_array($amount, $paymentInfo['allowed_amounts'])){
      $amount = $paymentInfo['amount'];
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

    $payment->lineItemTitle = $line_title;
    $payment->redirectDestination = $redirectDestination;

    $form = $this->entityFormBuilder->getForm($payment, 'payment_form');

    return [
      'form' => $form,
    ];
  }

  /**
   * @param $page
   */
  public function access($type) {
    $config = $this->getPaymentInfo($type);

    if (!empty($config)) {
      if (isset($config['permission']) && !$this->currentUser()->hasPermission($config['permission'])) {
        return AccessResult::forbidden();
      }

      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }
}
