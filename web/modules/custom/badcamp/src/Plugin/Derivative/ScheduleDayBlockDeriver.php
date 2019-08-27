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
class ScheduleDayBlockDeriver extends DeriverBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $days = [
      'Wed' => '62',
      'Thurs' => '63',
      'Fri' => '64',
      'Sat' => '65'
    ];

    $views = [
      'General' => 'general',
      'My Schedule' => 'my_schedule'
    ];
    foreach ($views AS $view_name => $view_id) {
      foreach ($days as $day => $id) {
        $this->derivatives[$view_id . ':' . $id] = [
            'id' => $view_id . ':' . $id,
            'label' => $view_name . ' - ' . $day,
            'admin_label' => $this->t('Schedule Block (@view) : @day', ['@day' => $day, '@view' => $view_name]),
          ] + $base_plugin_definition;
      }
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
