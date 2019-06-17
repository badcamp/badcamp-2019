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

    if ($page == 'register') {
      // If logged in redirect to the sponsors page.
      if ($this->currentUser()->isAuthenticated()) {
        return $this->generateResponse('sponsor');
      }

      // Check to see if user has a
      if ($this->currentUser()->hasPermission('allow users to register with badcamp_register')) {
        $url = Url::fromRoute('system.403');
        $response = new RedirectResponse($url->toString());
        return $response;
      }

      $entity = $this->entityTypeManager->getStorage('user')->create(array());
      $formObject = $this->entityTypeManager
        ->getFormObject('user', 'register')
        ->setEntity($entity);
      $form = $this->formBuilder->getForm($formObject);
      $rendered_form = $this->renderer->render($form);

      return [
        '#theme' => 'registration_page',
        '#content' => $rendered_form,
        '#step1active' => TRUE,
      ];
    }

    if ($page == 'sponsor' || $page == 'event') {
      if ($this->currentUser()->isAnonymous()) {
        return $this->generateResponse('register');
      }
    }

    if ($page == 'sponsor') {

      if (isset($config['bid'])) {
        $bid = $config['bid'];
        $block = $this->entityRepository->loadEntityByUuid('block_content', $bid);
        $render = $this->entityTypeManager->getViewBuilder('block_content')->view($block);
      }

      return [
        '#theme' => 'registration_page',
        '#content' => $render,
        '#step2active' => TRUE,
        '#intro_message' => check_markup($text, $format)
      ];
    }
    elseif ($page == 'event') {
      $content = [];
      if (isset($config['content'])) {
        $content = $config['content'];
      }

      return [
        '#theme' => 'registration_page',
        '#content' => $content,
        '#step3active' => TRUE,
        '#intro_message' => check_markup($text, $format)
      ];
    }


    return [
      '#markup' => 'test'
    ];
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
    $title = '';
    switch($page) {
      case 'register':
        $title = $this->t('Registration');
        break;
      case 'sponsor':
        $title = $this->t('Sponsor');
        break;
      case 'events':
        $title = $this->t('Events');
        break;
    }
    return $title;
  }

  /**
   * @param $page
   */
  public function access($page) {
    switch($page) {
      case 'register':
      case 'sponsor':
      case 'events':
        return AccessResult::allowed();
        break;
    }

    return AccessResult::forbidden();
  }

}
