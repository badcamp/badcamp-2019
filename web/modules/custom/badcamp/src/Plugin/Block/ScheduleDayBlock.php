<?php

namespace Drupal\badcamp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;

/**
 * Provides a 'Example: empty block' block.
 *
 * @Block(
 *   id = "schedule_day",
 *   deriver = "Drupal\badcamp\Plugin\Derivative\ScheduleDayBlockDeriver",
 * )
 */
class ScheduleDayBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    list(,$view_name,$day_id) = explode(':', $this->getPluginId());
    return [
      'schedule' => views_embed_view('schedule', $view_name, $day_id),
    ];
  }

}
