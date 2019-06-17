<?php

/**
 * @file
 * Contains \Drupal\badcamp_register\Routing\RouteSubscriber.
 */

namespace Drupal\badcamp_register\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

	/**
	 * {@inheritdoc}
	 */
	protected function alterRoutes(RouteCollection $collection) {
		// Get the route you want to alter
		if ($route = $collection->get('user.register')) {
			$route->setRequirement('_access', 'FALSE');
		}
	}
}