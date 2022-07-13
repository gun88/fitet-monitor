<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/titles/class-fitet-monitor-titles-component.php';


class Fitet_Monitor_Titles_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fitet-monitor-titles');
		$this->manager = $manager;
	}

	public function attributes(): array {
		return ['club', 'players-page-id'];
	}

	protected function process_attributes($attributes) {

		$configuration = $attributes;
		if (empty($configuration['club'])) {
			// no club found - keeping all
			$clubs = $this->manager->get_clubs();
		} else {
			$clubs = [$this->manager->get_club($configuration['club'])];
		}

		$titles = $this->extract_titles($clubs);


		$titles['playerPageUrl'] = 'index.php?page_id=' . $attributes['players-page-id'];

		if (count($clubs) > 1) {
			return ['mode' => 'multiClub', 'data' => $titles];
		}

		return ['mode' => 'standard', 'data' => $titles];
	}

	public function wrapped_component($mode) {
		return new Fitet_Monitor_Titles_Component($this->plugin_name, $this->version, $mode == 'multiClub');
	}

	private function extract_titles($clubs) {
		$national = array_map(function ($club) {
			$titles = $club['nationalTitles'];
			foreach ($titles as &$cap) {
				$cap['clubCode'] = $club['clubCode'];
				$cap['clubName'] = $club['clubName'];
			}

			return $titles;
		}, $clubs);
		$national = array_merge(...$national);

		$regional = array_map(function ($club) {
			$titles = $club['regionalTitles'];
			foreach ($titles as &$cap) {
				$cap['clubCode'] = $club['clubCode'];
				$cap['clubName'] = $club['clubName'];
			}

			return $titles;
		}, $clubs);
		$regional = array_merge(...$regional);

		return [
			'nationalTitles' => $national,
			'regionalTitles' => $regional,
		];
	}

}



