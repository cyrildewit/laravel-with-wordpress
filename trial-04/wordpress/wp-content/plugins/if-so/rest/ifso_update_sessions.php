<?php
	header('Content-Type: application/json');

	if ( isset($_POST['sessions']) ) {
		$sessions = $_POST['sessions'];

		echo json_encode(array('sessions' => $sessions));
	} else {
		echo json_encode(array('error' => true));
	}

?>