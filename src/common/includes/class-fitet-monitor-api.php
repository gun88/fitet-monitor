<?php

class Fitet_Monitor_Api {

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
		register_rest_route($this->plugin_name . '/v1', '/portal/find-clubs',
			['methods' => 'GET',
				'callback' => [$this, 'find_clubs'],
				'permission_callback' => function () {
					return current_user_can('edit_pages');
				}
			]
		);
        register_rest_route($this->plugin_name . '/v1', '/update',
            [
                'methods' => 'POST',
                'callback' => [$this, 'update'],
                'permission_callback' => function () {
                    return current_user_can('edit_pages');
                }
            ]
        );

        register_rest_route($this->plugin_name . '/v1', '/export',
            [
                'methods' => 'POST',
                'callback' => [$this, 'export'],
                'permission_callback' => function () {
                    return current_user_can('edit_pages');
                }
            ]
        );

		register_rest_route($this->plugin_name . '/v1', '/reset',
			[
				'methods' => 'POST',
				'callback' => [$this, 'reset'],
				'permission_callback' => function () {
					return current_user_can('edit_pages');
				}
			]
		);
		register_rest_route($this->plugin_name . '/v1', '/status',
			[
				'methods' => 'GET',
				'callback' => [$this, 'get_status'],
				'permission_callback' => function () {
					return current_user_can('edit_pages');
				}
			]
		);
		register_rest_route($this->plugin_name . '/v1', '/club',
			[
				'methods' => 'GET',
				'callback' => [$this, 'get_club'],
				'permission_callback' => function () {
					return current_user_can('edit_pages');
				}
			]
		);
	}

	public function find_clubs(WP_REST_Request $request) {
		$club_name_contains = $request->get_param('query');
		$clubs = $this->manager->find_clubs($club_name_contains);
		return rest_ensure_response($clubs);
	}

    public function update(WP_REST_Request $request) {
        $this->manager->update($request->get_param('clubCode'), $request->get_param('mode'), $request->get_param('seasonId'));
        return rest_ensure_response('done');
    }

    public function export(WP_REST_Request $request) {
        return rest_ensure_response($this->manager->export($request->get_param('clubCode')));
    }

	public function reset(WP_REST_Request $request) {
		$this->manager->reset_season($request->get_param('clubCode'), $request->get_param('seasonId'));
		return rest_ensure_response('done');
	}

	public function get_club(WP_REST_Request $request) {
		$club = $this->manager->get_club($request->get_param('clubCode'));
		return rest_ensure_response($club);
	}

	public function get_status(WP_REST_Request $request) {
		$status = $this->manager->get_status($request->get_param('clubCode'));
		return rest_ensure_response($status);
	}
}
