<?php

class Fitet_Monitor_Api { // todo sostituire con chiamate wordpress

	private $plugin_name;
	private $version;
	private $manager;

	/**
	 * @param string $plugin_name
	 * @param string $version
	 * @param Fitet_Monitor_Manager $manager
	 */
	public function __construct($plugin_name, $version, $manager) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->manager = $manager;
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
		$clubs = $this->manager->find_clubs($club_name_contains);
		return rest_ensure_response($clubs);
	}

	public function update(WP_REST_Request $request)
	{
		$configuration = $this->manager->update($request->get_param('clubCode'));
		return rest_ensure_response($configuration);
	}

	public function get_status(WP_REST_Request $request)
	{
		$status = $this->manager->get_status($request->get_param('clubCode'));
		return rest_ensure_response($status);
	}
}
