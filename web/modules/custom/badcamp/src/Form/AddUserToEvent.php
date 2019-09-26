<?php

namespace Drupal\badcamp\Form;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AddUserToEvent
 *
 * @package Drupal\badcamp\Form
 */
class AddUserToEvent extends FormBase {

  /**
   * Entity Type Manager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Flag Service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * Add to Schedule Flag.
   *
   * @var \Drupal\flag\FlagInterface
   */
  protected $flag;

  /**
   * AddUserToEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Type Manager service.
   * @param \Drupal\flag\FlagServiceInterface $flagService
   *   The Flag Service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FlagServiceInterface $flagService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->flagService = $flagService;
    $this->flag = $this->flagService->getFlagById('add_to_schedule');
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('flag')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = null) {
    $form['event'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_settings' => [
        'target_bundles' => [
          'event',
          'session',
          'summit',
          'training'
        ],
      ],
      '#title' => $this->t('Event Title'),
      '#description' => $this->t('The event which a person should be added to.'),
      '#required' => TRUE,
    ];

    if (!is_null($node)) {
      $form['event']['#default_value'] = $node;
      $form['event']['#disabled'] = true;
    }

    $form['user'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('User Name'),
      '#description' => $this->t('The user who should be added to the event.'),
      '#required' => TRUE,
    ];

    $form['action'] = [
      '#type' => 'radios',
      '#options' => [
        'add' => $this->t('Add In'),
        'remove' => $this->t('Remove Out')
      ],
      '#title' => $this->t('Action'),
      '#default_value' => 'add',
      '#required' => true,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'add_user_to_event';
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user_id = $form_state->getValue('user');
    $event_id = $form_state->getValue('event');
    $action = $form_state->getValue('action');

    if (!is_null($user_id) && !is_null($event_id)) {
      /** @var \Drupal\node\NodeInterface $entity */
      $entity = $this->entityTypeManager->getStorage('node')->load($event_id);
      /** @var \Drupal\user\UserInterface $user */
      $user = $this->entityTypeManager->getStorage('user')->load($user_id);

      if ($user && $entity) {
        try {
          if ($action == 'add') {
            $this->flagService->flag($this->flag, $entity, $user);
          }
          elseif ($action == 'remove') {
            $this->flagService->unflag($this->flag, $entity, $user);
          }
        } catch (\LogicException $e) {

        }
      }
    }
  }
}
