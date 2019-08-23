<?php

namespace Drupal\badcamp\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'badcamp_payment_field' field type.
 *
 * @FieldType(
 *   id = "badcamp_payment_field",
 *   label = @Translation("BADCamp Payment"),
 *   category = @Translation("Payment"),
 *   description = @Translation("Accept Payments using Payment Field"),
 *   default_widget = "badcamp_payment_field",
 *   default_formatter = "badcamp_payment_field"
 * )
 */
class PaymentField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    $defaultStorageSettings = [
        'enable' => 0,
        'amount' => 0,
        'description_value' => '',
        'description_format' => 'basic_html',
        'button_text' => '',
        'max_payments' => 0,
        'max_purchases' => 0,
      ] + parent::defaultStorageSettings();

    return $defaultStorageSettings;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['enable'] = DataDefinition::create('boolean')
      ->setLabel(t('Enabled'));

    $properties['amount'] = DataDefinition::create('integer')
      ->setLabel(t('Amount'));

    $properties['payment_type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Payment_Type'));

    $properties['description_value'] = DataDefinition::create('string')
      ->setLabel(t('Description'));

    $properties['button_label'] = DataDefinition::create('string')
      ->setLabel(t('Button Label'));

    $properties['description_format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Description format'));

    $properties['max_payments'] = DataDefinition::create('integer')
      ->setLabel(t('Max # of Payments'));

    $properties['max_purchases'] = DataDefinition::create('integer')
      ->setLabel(t('Max # of Purchases'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'amount' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'size' => 'big',
        ],
        'max_payments' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'size' => 'big',
        ],
        'max_purchases' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'size' => 'big',
        ],
        'enable' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
        'description_value' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'description_format' => [
          'type' => 'varchar_ascii',
          'length' => 255,
        ],
        'payment_type' => [
          'type' => 'varchar_ascii',
          'length' => 255,
          'not null' => TRUE,
        ],
        'button_label' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $enabled = $this->get('enable')->getValue() == 1;
    $value = $this->get('amount')->getValue();
    return !$enabled && ($value === NULL || $value === 0);
  }

}
