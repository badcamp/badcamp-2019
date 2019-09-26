<?php

namespace Drupal\badcamp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\badcamp\Form\AddUserToEvent as AddUserToEventForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add User to Event Block
 *
 * @Block(
 *   id = "badcamp_add_user_to_event",
 *   admin_label = @Translation("Add User to Event")
 * )
 */
class AddUserToEvent extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [
      'description' => [
        '#markup' => $this->t('Using form provided by @classname', ['@classname' => AddUserToEventForm::class]),
      ],
    ];

    $output['form'] = $this->formBuilder->getForm(AddUserToEventForm::class);
    return $output;
  }

}
