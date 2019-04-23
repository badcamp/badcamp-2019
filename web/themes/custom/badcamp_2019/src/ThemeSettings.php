<?php

namespace Drupal\bay_area_camp;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Theme\ThemeSettings as CoreThemeSettings;
use Drupal\Component\Utility\DiffArray;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\StorageException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a configuration API wrapper for runtime merged theme settings.
 *
 * This is a wrapper around theme_get_setting() since it does not inherit
 * base theme config nor handle default/overridden values very well.
 *
 * @ingroup utility
 */
class ThemeSettings extends Config {

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['rendered'];
  }


}