<?php

require_once FITET_MONITOR_DIR . 'admin/components/progress-bar/class-fitet-monitor-progress-bar-component.php';

class Fitet_Monitor_Club_Cell_Component extends Fitet_Monitor_Component {

	public function script_dependencies(): array {
		return ['jquery', 'wp-api'];
	}

	public function components() {
		return [
			'progressBar' => new Fitet_Monitor_Progress_Bar_Component($this->plugin_name, $this->version),
		];
	}

	public function process_data($data) {
		$table = $data['parentTable'];
		$club_code = $data['clubCode'];
		$club_name = $data['clubName'];
		$club_province = $data['clubProvince'];
		$club_logo = $data['clubLogo'];
		$status = $data['status'];
		$club_url = add_query_arg(['clubCode' => $club_code, 'mode' => 'club',], esc_url(menu_page_url($this->plugin_name, false)));
		$club_detail_url = add_query_arg(['clubCode' => $club_code, 'mode' => 'detail',], esc_url(menu_page_url($this->plugin_name, false)));

		$label_view = __('View');
		$label_edit = __('Edit');
		$label_update = __('Update');
		$label_delete = __('Delete');
		$label_delete_permanently = __('Delete Permanently');
		$label_cancel = __('Cancel');
		$label_retry = __('Retry');


		$_data = [];
		$_data['clubCode'] = $club_code;
		$_data['clubName'] = $club_name;
		$_data['clubProvince'] = $club_province;
		$_data['clubLogo'] = $club_logo;
		$_data['clubNoLogo'] = FITET_MONITOR_CLUB_NO_LOGO;
		$_data['clubUrl'] = $club_url;
		$_data['clubDetailUrl'] = $club_detail_url;
		$_data['progressBar'] = $this->components['progressBar']->render();
		$_data['deleteDisclaimer'] = "The club <b>$club_name</b> will be permanently deleted and all changes will be lost.";


		$_data['mainRowActions'] = $table->row_actions([
			'view' => "<a href='$club_detail_url'>$label_view</a>",
			'edit' => "<a href='$club_url'>$label_edit</a>",
			'update' => "<button type='button' class='button-link fm-btn-update' data-club-code='$club_code' data-club-name='$club_name' data-club-logo='$club_logo'>$label_update</button>",
			"delete" => "<button type='button' class='button-link fm-btn-delete' data-club-code='$club_code' data-club-name='$club_name' data-club-logo='$club_logo'>$label_delete</button>",
		]);

		$_data['deleteRowActions'] = $table->row_actions([
			'delete' => "<button type='button' class='button-link' data-value='delete' data-club-code='$club_code'>$label_delete_permanently</button>",
			'restore' => "<button type='button' class='button-link' data-value='restore'>$label_cancel</button>",
		]);

		$_data['updateRowActions'] = $table->row_actions([
			'retry' => "<button type='button' class='button-link fm-btn-update' data-club-code='$club_code' data-club-name='$club_name' data-club-logo='$club_logo'>$label_retry</button>",
			'restore' => "<button type='button' class='button-link fm-btn-cancel' data-value='restore'>$label_cancel</button>",
		], true);

		$_data['status'] = $status;

		return $_data;
	}
}
