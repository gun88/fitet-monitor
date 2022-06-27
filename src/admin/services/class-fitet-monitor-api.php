<?php

class Fitet_Monitor_Api { // todo sostituire con chiamate wordpress

	private $plugin_name;
	private $version;
	private $portal;

	/**
	 * @param string $plugin_name
	 * @param string $version
	 * @param Fitet_Portal_Rest $portal
	 */
	public function __construct($plugin_name, $version, $portal) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->portal = $portal;
	}

	public function initialize() {
		register_rest_route($this->plugin_name . '/v1', '/portal/clubs',
			['methods' => 'GET',
				'callback' => [$this, 'find_clubs'],
				'permission_callback' => function () {
					return current_user_can('edit_pages');
				}
			]
		);
		register_rest_route('apex-api/v1', '/update',
			array(
				'methods' => 'POST',
				'callback' => [$this, 'update'],
				'permission_callback' => function () {
					return current_user_can('edit_pages');
				}
			)
		);
		register_rest_route('apex-api/v1', '/status',
			array(
				'methods' => 'GET',
				'callback' => [$this, 'get_status'],
				'permission_callback' => function () {
					return current_user_can('edit_pages');
				}
			)
		);
	}

	public function find_clubs(WP_REST_Request $request) {
		$club_name_contains = $request->get_param('query');
		$clubs = $this->portal->find_clubs($club_name_contains);
		return rest_ensure_response($clubs);
	}
}
