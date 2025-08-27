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

        register_rest_route($this->plugin_name . '/v1', '/resetRid',
            [
                'methods' => 'GET',
                'callback' => [$this, 'reset_players_ranking_id'],
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

        register_rest_route($this->plugin_name . '/v1', '/upload-player-image', [
            'methods'  => 'POST',
            'callback' => [$this, 'upload_player_image'],
            'permission_callback' => function () {
                return current_user_can('upload_files'); // solo utenti autorizzati
            },
        ]);
        register_rest_route($this->plugin_name . '/v1', '/delete-player-image', [
            'methods'  => 'POST',
            'callback' => [$this, 'delete_player_image'],
            'permission_callback' => function () {
                return current_user_can('upload_files'); // solo utenti autorizzati
            },
        ]);

        register_rest_route( 'fitet-monitor/v1', '/create_pages', array(
            'methods'             => 'POST',
            'callback'            => [$this, 'create_pages'],
            'permission_callback' => function () {
                return current_user_can( 'edit_pages' );
            },
        ) );
	}

    public function reset_players_ranking_id(WP_REST_Request $request) {
        $this->manager->reset_players_ranking_id($request->get_param('clubCode'));
        return rest_ensure_response('done');
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

    public function delete_player_image(WP_REST_Request $request) {
        $playerCode = sanitize_file_name($request->get_param('code'));
        if (!$playerCode) {
            return new WP_Error('no_code', 'Nessun player code passato', ['status' => 400]);
        }

        $response = $this->manager->delete_player_image($playerCode);
        return rest_ensure_response($response);

    }
    public function upload_player_image(WP_REST_Request $request) {
        if (empty($_FILES['file'])) {
            return new WP_Error('no_file', 'Nessun file ricevuto', ['status' => 400]);
        }

        $file = $_FILES['file'];
        $playerCode = sanitize_file_name($request->get_param('code'));

        if (!$playerCode) {
            return new WP_Error('no_code', 'Nessun player code passato', ['status' => 400]);
        }

        // Estensione in minuscolo
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png','jpg','jpeg'])) {
            return new WP_Error('invalid_ext', 'Estensione non valida', ['status' => 400]);
        }

        $response = $this->manager->upload_player_image($file,$playerCode,$ext);
        if ($response == -1) {
            return new WP_Error('move_failed', 'Impossibile spostare il file', ['status' => 500]);
        }
        return rest_ensure_response($response);
    }


    public function create_pages(WP_REST_Request $request) {
        $response = $this->manager->create_pages($request);
        return rest_ensure_response($response);
    }
}
