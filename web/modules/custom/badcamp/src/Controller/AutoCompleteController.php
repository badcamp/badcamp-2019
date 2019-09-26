<?php

namespace Drupal\badcamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Element\EntityAutocomplete;
/**
 * Class AutoCompleteController.
 */
class AutoCompleteController extends ControllerBase {

  /**
   * AutoCompleteController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Return a list of all Events.
   */
  public function handleEventAutocomplete(Request $request) {
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $results = [];
    $input = $request->query->get('q');
    // Get the typed string from the URL, if it exists.
    if (!$input) {
      return new JsonResponse($results);
    }
    $input = Xss::filter($input);
    $query = $nodeStorage->getQuery()
      ->condition('title', $input, 'CONTAINS')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', ['event', 'session', 'training', 'summit'])
      ->groupBy('nid')
      ->sort('created', 'DESC')
      ->range(0, 10);
    $ids = $query->execute();
    $nodes = $ids ? $nodeStorage->loadMultiple($ids) : [];
    foreach ($nodes as $node) {
      $label = [
        $node->getTitle(),
      ];
      $results[] = [
        'value' => EntityAutocomplete::getEntityLabels([$node]),
        'label' => implode(' ', $label),
      ];
    }
    return new JsonResponse($results);
  }

  /**
   * Return a list of all users.
   */
  public function handleUserAutocomplete(Request $request) {
    $userStorage = $this->entityTypeManager->getStorage('user');
    $results = [];
    $input = $request->query->get('q');
    // Get the typed string from the URL, if it exists.
    if (!$input) {
      return new JsonResponse($results);
    }
    $input = Xss::filter($input);
    $query = $userStorage->getQuery()
      ->condition('name', $input, 'CONTAINS')
      ->condition('status', 1)
      ->groupBy('uid')
      ->sort('name', 'ASC')
      ->range(0, 10);
    $ids = $query->execute();
    $users = $ids ? $userStorage->loadMultiple($ids) : [];
    /** @var \Drupal\user\Entity\User $user */
    foreach ($users as $user) {
      $label = [
        $user->getDisplayName(),
      ];
      $results[] = [
        'value' => EntityAutocomplete::getEntityLabels([$user]),
        'label' => implode(' ', $label),
      ];
    }
    return new JsonResponse($results);
  }
}
