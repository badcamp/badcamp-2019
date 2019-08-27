<?php

namespace Drupal\badcamp\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Plugin implementation of the 'stripe_payment' formatter.
 *
 * @FieldFormatter(
 *   id = "badcamp_payment_field",
 *   module = "badcamp",
 *   label = @Translation("BADCamp Payment"),
 *   field_types = {
 *     "badcamp_payment_field"
 *   }
 * )
 */
class PaymentField extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityTypeBundleInfo definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   */
  protected $bundleInfo;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * StripePayment constructor.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    EntityTypeManager $entityManager,
    EntityTypeBundleInfo $bundleInfo,
    AccountProxy $account
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entityManager;
    $this->bundleInfo = $bundleInfo;
    $this->currentUser = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    if (count($items) > 0) {
      $entity = $items->getEntity();
      $total_payments = $this->getNumberOfPayments($entity->id(), $this->currentUser->id());
      $total_purchases = $this->getNumberOfPayments($entity->id());
      $item = $items->get(0);
      if (($total_payments < $item->max_payments || $item->max_payments == 0) && $item->enable && ($total_purchases < $item->max_purchases || $item->max_purchases == 0)) {
        $payment_label = $this->bundleInfo->getBundleInfo('payment')[$item->payment_type]['label'];

        $footer = '';
        if( $item->max_purchases > 0) {
          $footer = $this->t('@count Spaces Available', ['@count' => ($item->max_purchases - $total_purchases)]);
        }
        $elements[] = [
          '#theme' => 'payment_button',
          '#description' => $item->description_value,
          '#payment_type' => $item->payment_type,
          '#button_label' => $item->button_label,
          '#button' => Link::createFromRoute($item->button_label, 'badcamp.payment', [
            'type' => $item->payment_type
          ], [
            'attributes' =>  [
              'class'=>'use-ajax',
              'data-dialog-type' => "modal",
              'data-dialog-options' => '{"width":800}',
            ],
            'query' => [
              'amount' => $item->amount,
              'entity_type' => $entity->getEntityType()->id(),
              'entity_id' => $entity->id()
            ]
          ]),
          '#footer' => $footer,
        ];
      }
      elseif (!$item->enable){

      }
      elseif ($total_payments >= $item->max_payments) {
        $elements[] = [
          '#markup' => '<div class="payment-message paid-message">' . $this->t('Paid!') . '</div>',
        ];
      }
      elseif ($total_purchases >= $item->max_purchases){
        $elements[] = [
          '#markup' => '<div class="payment-message soldout-message">' . $this->t('Sold Out') . '</div>',
        ];
      }
    }
    $elements['#cache']['max-age'] = 0;
    return $elements;
  }

  /**
   * Get the number of payments for the provided entity.
   * Check also to see how many are for a particular user.
   */
  private function getNumberOfPayments($entity_id, $user_id = NULL) {
    $query = $this
      ->entityTypeManager
      ->getStorage('payment')
      ->getQuery('AND')
      ->condition('field_training', $entity_id);

    if(!is_null($user_id)) {
      $query->condition('owner', $user_id);
    }

    return $query
      //->condition('') only select Completed "payment_success"
      ->count()
      ->execute();
  }
}
