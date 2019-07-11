<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Place the config directory outside of the Drupal root.
 */
$config_directories = array(
  CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config',
);

global $content_directories;
$content_directories['sync'] = dirname(DRUPAL_ROOT) . '/content/sync';

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

/**
 * Always install the 'standard' profile to stop the installer from
 * modifying settings.php.
 */
$settings['install_profile'] = 'standard';

/**
 * Load in the api keys from the JSON file in the private files.
 */
$config['stripe.settings']['environment'] = 'test';
if (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live') {
  $json_text = file_get_contents('sites/default/files/private/secrets.json');
  $key_data = json_decode($json_text, TRUE);
  $config['mailchimp.settings']['api_key'] = $key_data['mailchimp_key'];
  $config['sendgrid_integration.settings']['apikey'] = $key_data['sendgrid_api'];
  $config['google_analytics.settings']['account'] = $key_data['google_analytics'];
  $config['stripe.settings']['environment'] = 'live';
  $config['stripe.settings']['apikey']['live']['public'] = $key_data['stripe_live_public'];
  $config['stripe.settings']['apikey']['live']['secret'] = $key_data['stripe_live_secret'];
  $config['stripe.settings']['apikey']['test']['public'] = $key_data['stripe_test_public'];
  $config['stripe.settings']['apikey']['test']['secret'] = $key_data['stripe_test_secret'];
}
else {
  $json_text = file_get_contents('sites/default/files/private/secrets.json');
  $key_data = json_decode($json_text, TRUE);
  $config['mailchimp.settings']['api_key'] = $key_data['mailchimp_key'];
  $config['stripe.settings']['apikey']['live']['public'] = '';
  $config['stripe.settings']['apikey']['live']['secret'] = '';
  $config['stripe.settings']['apikey']['test']['public'] = $key_data['stripe_test_public'];
  $config['stripe.settings']['apikey']['test']['secret'] = $key_data['stripe_test_secret'];
  $config['system.logging']['error_level'] = 'verbose';
}

// Require HTTPS
if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && ($_SERVER['HTTPS'] === 'OFF') && (php_sapi_name() != "cli")) {
  if (!isset($_SERVER['HTTP_X_SSL']) || (isset($_SERVER['HTTP_X_SSL']) && $_SERVER['HTTP_X_SSL'] != 'ON')) {
    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
  }
}

// Require 2018.badcamp.org Domain
if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && ($_SERVER['PANTHEON_ENVIRONMENT'] === 'live') && (php_sapi_name() != "cli")) {
  if ($_SERVER['HTTP_HOST'] != '2019.badcamp.org' || !isset($_SERVER['HTTP_X_SSL']) || $_SERVER['HTTP_X_SSL'] != 'ON' ) {
    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://2019.badcamp.org'. $_SERVER['REQUEST_URI']);
    exit();
  }
}

// Prevent Config Changes in Production
if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && ($_SERVER['PANTHEON_ENVIRONMENT'] === 'live')) {
  $settings['config_readonly'] = FALSE;
}

if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && ($_SERVER['PANTHEON_ENVIRONMENT'] != 'live' && $_SERVER['PANTHEON_ENVIRONMENT'] != 'test')) {
  $config['system.logging']['error_level'] = ERROR_REPORTING_DISPLAY_VERBOSE;
}

# Reverse proxy configuration (Docksal's vhost-proxy)
if (PHP_SAPI !== 'cli') {
  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_addresses'] = array($_SERVER['REMOTE_ADDR']);
  // HTTPS behind reverse-proxy
  if (
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' &&
    !empty($settings['reverse_proxy']) && in_array($_SERVER['REMOTE_ADDR'], $settings['reverse_proxy_addresses'])
  ) {
    $_SERVER['HTTPS'] = 'on';
    // This is hardcoded because there is no header specifying the original port.
    $_SERVER['SERVER_PORT'] = 443;
  }
}
