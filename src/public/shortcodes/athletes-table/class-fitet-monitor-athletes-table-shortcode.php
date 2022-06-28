<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-bootstrap-table.php';

class Fitet_Monitor_Athletes_Table_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fm-athletes-table');
		$this->manager = $manager;
	}

	public function enqueue_scripts() {
		parent::enqueue_scripts();
		$file = FITET_MONITOR_DIR . "public/assets/bootstrap-table.js";
		$file = plugin_dir_path($file) . basename($file);
		Fitet_Monitor_Helper::enqueue_script("bootstrap-table.js", $file, ['jquery'], $this->version, false);
	}

	public function enqueue_styles() {
		parent::enqueue_styles();
		$file = FITET_MONITOR_DIR . "public/assets/fitet-monitor-bootstrap.css";
		$file = plugin_dir_path($file) . basename($file);
		Fitet_Monitor_Helper::enqueue_style("bootstrap.css", $file, [], $this->version, 'all');

	}

	protected function process_data($data) {

		$attributes = $data['attributes'];
		$content = $data['content'];

		$attributes = shortcode_atts(
			['club-code' => '',]
			, $attributes, $this->tag);

		$club_code = $attributes['club-code'];
		if (empty($club_code)) {
			$clubs = $this->manager->get_clubs();
			if (!isset($clubs[0]))
				throw new Exception("No club found");
			$club = $clubs[0];
		} else {
			$club = $this->manager->get_clubs($club_code);
		}

		$players_table = $this->player_table();
		$table = $players_table->render($club['players']);

		return [
			'table' => $table
		];
	}

	public function player_table() {
		$columns = [];
		$columns['name'] = __('Name');
		$columns['rank'] = __('Rank');
		$columns['points'] = __('Points');
		$columns['category'] = __('Category');
		$columns['sector'] = __('Sector');
		$columns['diff'] = __('Diff');
		$columns['birthDate'] = __('BirthDate');
		$columns['sex'] = __('Sex');
		$columns['code'] = __('Code');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}



}
