<?php

namespace Drupal\badcamp\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MyScheduleController.
 */
class MyScheduleController extends ControllerBase {

  /**
   * Account Proxy Service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface $accountProxy
   */
  protected $accountProxy;

  /**
   * Block Manager Service.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface $blockManager
   */
  protected $blockManager;

  /**
   * MyScheduleController constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $accountProxy
   *   The account proxy service.
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   *   The block manager service.
   */
  public function __construct(AccountProxyInterface $accountProxy, BlockManagerInterface $blockManager) {
    $this->accountProxy = $accountProxy;
    $this->blockManager = $blockManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Title callback.
   */
  public function title(UserInterface $user) {
    return $this->accountProxy->id() == $user->id() ? $this->t('My Schedule') : $this->t("@name's Schedule", ['@name' => $user->getDisplayName()]);
  }

  /**
   * Return the output of the schedule page.
   */
  public function schedule(UserInterface $user) {

    $config = [];
    $plugin_block = $this->blockManager->createInstance('quicktabs_block:my_schedule', $config);
    // Some blocks might implement access check.
    $access_result = $plugin_block->access(\Drupal::currentUser());
    // Return empty render array if user doesn't have access.
    // $access_result can be boolean or an AccessResult class
    if (is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result) {
      // You might need to add some cache tags/contexts.
      return [];
    }
    $render = $plugin_block->build();

    return [
      'block' => $render,
    ];
  }

  /**
   * Return if someone has access to the page.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user being passed in.
   *
   * @return \Drupal\Core\Access\AccessResult
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function access(UserInterface $user) {
    $allowAccess = $user->get('field_options')->getValue();
    foreach($allowAccess AS $values) {
      if ($values['value'] == 'share') {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

}
