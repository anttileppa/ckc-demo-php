<?php

require_once dirname( __FILE__ ) . '/../../../settings.php';

function db_connect() {
	global $ckc_settings;
  $mysqli = new mysqli($ckc_settings->hostname, $ckc_settings->username, $ckc_settings->password, $ckc_settings->database);
  if ($mysqli->connect_errno) {
  	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
  	die;
  }
  
	return $mysqli;
}

function db_close($mysqli) {
	$mysqli->close();
}

?>