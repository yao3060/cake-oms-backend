<?php

/**
 * Filters user data returned from the REST API.
 *
 * @since 4.7.0
 *
 * @param WP_REST_Response $response The response object.
 * @param WP_User          $user     User object used to create response.
 * @param WP_REST_Request  $request  Request object.
 */
add_filter('rest_prepare_user', function ($response, $user, $request) {
	$groups = wp_get_terms_for_user($user, 'user-group');
	if ($groups && is_array($groups)) {
		foreach ($groups as $key => $group) {
			$response->data['stores'][] = [
				'id' => $group->term_id,
				'name' => $group->name,
				'slug' => $group->slug
			];
		}
	}

	return $response;
}, 10, 3);
