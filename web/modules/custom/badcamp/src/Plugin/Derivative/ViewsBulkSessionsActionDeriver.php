<?php

namespace Drupal\badcamp\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generate session actions plugins for each flag.
 */
class ViewsBulkSessionsActionDeriver extends DeriverBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $actions = [
      'pending' => 'Pending',
      'maybe' => 'Maybe',
      'approved' => 'Approved',
      'rejected' => 'Rejected'
    ];

    foreach ($actions as $action => $label) {
      $this->derivatives[$action] = [
          'id' => $action,
          'label' => $label,
        ] + $base_plugin_definition;
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
