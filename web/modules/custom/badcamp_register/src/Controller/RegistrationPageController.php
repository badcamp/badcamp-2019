<?php

namespace Drupal\badcamp_register\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class RegistrationPageController
 *
 * @package Drupal\badcamp_register\Controller
 */
class RegistrationPageController extends ControllerBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The Form Builder Service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The Entity Type Manager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Renderer Service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The Entity Repository Service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * RegistrationPageController constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   */
  public function __construct(AccountInterface $account, FormBuilderInterface $formBuilder, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer, EntityRepositoryInterface $entityRepository) {
    $this->account = $account;
    $this->formBuilder = $formBuilder;
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('form_builder'),
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  /**
   * Page Callback.
   *
   * @param $page
   *   The page name to use
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function page($page) {
    $config = $this->config('badcamp_register.settings')->get($page);

    $text = isset($config['message']) ? $config['message']['value'] : '';
    $format = isset($config['message']) ? $config['message']['format'] : NULL;
    $render = '';

    $config = array_merge([
      'requires_auth' => FALSE,
    ], $config);

    $data = [
      '#theme' => 'registration_page',
    ];

    $active = isset($config['active']) ? $config['active'] : FALSE;

    // If Block Set.
    if (isset($config['content']['block'])) {
      if (isset($config['content']['block']['bid'])) {
        $bid = $config['content']['block']['bid'];
        $block = $this->entityRepository->loadEntityByUuid('block_content', $bid);
        $render = $this->entityTypeManager->getViewBuilder('block_content')
          ->view($block);
      }
    }
    elseif (isset($config['content']['entity_form'])) {

      $entity_form = isset($config['content']['entity_form']) ? $config['content']['entity_form'] : [];
      $entity_type = isset($entity_form['entity_type']) ? $entity_form['entity_type'] : FALSE;
      $display_mode = isset($entity_form['display_mode']) ? $entity_form['display_mode'] : FALSE;
      $default_data = isset($entity_form['default_data']) ? $entity_form['default_data'] : [];

      if ($entity_type !== FALSE && $display_mode !== FALSE) {
        $entity = $this->entityTypeManager->getStorage($entity_type)
          ->create($default_data);
        $formObject = $this->entityTypeManager
          ->getFormObject($entity_type, $display_mode)
          ->setEntity($entity);
        $form = $this->formBuilder->getForm($formObject);
        $render = $this->renderer->render($form);
      }
    }
    // Default to whatever is in content.
    elseif (isset($config['content']) && !empty($config['content'])) {
      $render = $config['content'];
    }

    if (($config['requires_auth'] === TRUE && !$this->currentUser()->isAuthenticated()) ||
        ($config['requires_auth'] === FALSE && $this->currentUser()->isAuthenticated())) {
      return $this->generateResponse($config['redirect_auth']);
    }

    if ($active !== FALSE) {
      $data['#' . $active] = TRUE;
    }

    if ($text !== '') {
      $data['#intro_message'] = check_markup($text, $format);
    }

    $data['#content'] = $render;

    return $data;
  }

  /**
   * @param $page
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  private function generateResponse($page) {
    $path = Url::fromRoute('badcamp_register.page', ['page' => $page])->toString();
    $response = new RedirectResponse($path);
    return $response;
  }

  /**
   * @param $page
   */
  public function title($page) {
    $config = $this->config('badcamp_register.settings')->get($page);

    if (isset($config['title']))
      return $config['title'];

    return '';
  }

  /**
   * @param $page
   */
  public function access($page) {
    $config = $this->config('badcamp_register.settings')->get($page);

    if (!empty($config)) {
      if (isset($config['permission']) && !$this->currentUser()->hasPermission($config['permission'])) {
        return AccessResult::forbidden();
      }

      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
