<?php

namespace Drupal\badcamp\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'stripe_payment' widget.
 *
 * @FieldWidget(
 *   id = "badcamp_payment_field",
 *   module = "badcamp",
 *   label = @Translation("BADCamp Payment"),
 *   field_types = {
 *     "badcamp_payment_field"
 *   }
 * )
 */
class PaymentField extends WidgetBase implements ContainerFactoryPluginInterface {

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
   * Constructor.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    EntityTypeManager $entityManager,
    EntityTypeBundleInfo $bundleInfo,
    AccountProxy $account
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
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
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#theme_wrappers'][] = 'fieldset';

    $element['enable'] = [
      '#title' => t('Enable Payment For Field'),
      '#type' => 'checkbox',
      '#default_value' => isset($items[$delta]->enable) ? $items[$delta]->enable : 0,
    ];

    $states = [
      '#states' => [
        'invisible' => [
          ':input[name="' . $this->fieldDefinition->getName() . '[' . $delta . '][enable]"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $element['amount'] = [
      '#type' => 'number',
      '#title' => t('Amount'),
      '#description' => t('The amount to charge a person for this event.'),
      '#default_value' => isset($items[$delta]->amount) ? $items[$delta]->amount : 0,
    ] + $states;

    $element['max_payments'] = [
      '#type' => 'number',
      '#title' => t('Max # of Payments'),
      '#description' => t('How many times can someone try to purchase this item. 0 = Unlimited.'),
      '#default_value' => isset($items[$delta]->max_payments) ? $items[$delta]->max_payments : 0,
    ] + $states;

    $element['max_purchases'] = [
      '#type' => 'number',
      '#title' => t('Max # of Purchases'),
      '#description' => t('Total number of times everyone can purchase combined. 0 = Unlimited.'),
      '#default_value' => isset($items[$delta]->max_purchases) ? $items[$delta]->max_purchases : 0,
    ] + $states;

    $element['button_label'] = [
      '#type' => 'textfield',
      '#title' => t('Button Label'),
      '#description' => t('This is the label of the button that a user will click'),
      '#default_value' => isset($items[$delta]->button_label) ? $items[$delta]->button_label : '',
    ] + $states;

    $element['description'] = [
      '#type' => 'text_format',
      '#title' => t('Description'),
      '#description' => t('The description of the item you are asking the buyer to pay for.'),
      '#default_value' => isset($items[$delta]->description_value) ? $items[$delta]->description_value : '',
      '#format' => isset($items[$delta]->description_format) ? $items[$delta]->description_format : 'basic_html',
    ] + $states;

    if ($definitions = $this->bundleInfo->getBundleInfo('payment')) {
      $bundle_options = [];
      if (isset($definitions) && is_array($definitions)) {
        foreach ($definitions as $definition_key => $definition) {
          $bundle_options[$definition_key] = $definition['label'];
        }
      }

      $element['payment_type'] = [
        '#type' => 'select',
        '#title' => t('Type'),
        '#description' => t('The type of payment'),
        '#required' => TRUE,
        '#options' => $bundle_options,
        '#default_value' => isset($items[$delta]->payment_type) ? $items[$delta]->payment_type : '',
      ] + $states;

      return $element;

    }
    else {
      drupal_set_message(t('You need to specify a payment type first'), 'error');
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);

    $new_values = $values;
    foreach ($values as $k => $v) {
      $new_values[$k]['description_value'] = $values[$k]['description']['value'];
      $new_values[$k]['description_format'] = $values[$k]['description']['format'];
    }
    return $new_values;
  }

}
