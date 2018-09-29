<?php

/**
 * This class handles all logic related to visitors tracker feature
 *
 * @author     Matan Green <matangrn@gmail.com>
 */

if (!class_exists('IfSo_Page_Visits_Service')) {
class IfSo_Page_Visits_Service {

	private static $instance; 

	private function __construct() {
		$this->cookie_name = 'ifso_page_visits';
		$this->cookie_save_time_interval = 86400 * 356 * 3;
	}

	public static function getInstance() {
		if ( NULL == self::$instance )
			self::$instance = new IfSo_Page_Visits_Service();

		return self::$instance;
	}

	private function get_cookie_name() {
		return $this->cookie_name;
	}

	private function get_cookie_interval() {
		return $this->cookie_save_time_interval;
	}

	private function create_new_page_vists() {
		$this->save_pages(array());
	}

	private function get_page_visits() {
		$cookie_name = $this->get_cookie_name();

		if ( isset($_COOKIE[$cookie_name]) )
			return json_decode(stripslashes($_COOKIE[$cookie_name]), true);
		else {
			$this->create_new_page_vists();
			return array();
		}
	}

	private function save_pages($page_visits) {
		$cookie_name = $this->get_cookie_name();
		$save_time = time() + $this->get_cookie_interval();
		$encoded_page_visits = json_encode($page_visits, JSON_UNESCAPED_UNICODE);
		setcookie($cookie_name, $encoded_page_visits, $save_time, '/');
	}

	public function is_visited($page_id) {
		$page_visits = $this->get_page_visits();
		return in_array($page_id, $page_visits);
	}

	public function save_page_id($page_id) {
		// check if we already saved $page_id
		if ($this->is_visited($page_id)) return;

		$page_visits = $this->get_page_visits();
		$page_visits[] = $page_id;
		$this->save_pages($page_visits);
	}

	public function remove_page_id($page_id) {
		// check that $page_id exists
		if (!$this->is_visited($page_id)) return;

		$page_visits = $this->get_page_visits();
		$key = array_search($page_id, $page_visits);
		
		if ($key !== false) {
			unset($page_visits[$key]);
			$this->save_pages($page_visits);
		}
	}

}}