<?php

require_once dirname( __FILE__ ) . '/../settings.php';

function register_ckc_connector($name, $connector_class) {
	global $ckc_connector;
	
	if (!isset($ckc_connector)) {
		global $ckc_settings;
		
		if ($ckc_settings->connector == $name) {
  		$ckc_connector = new $connector_class();
		}
  }
}

foreach (glob(dirname( __FILE__ ) . "/connectors/*/connector.php") as $connector_file) {
	require_once $connector_file;
};

function getConnector() {
	global $ckc_connector;
	return $ckc_connector;
}

function getToken() {
	$headers = getallheaders();
	$authorizationHeader = $headers['Authorization'];
	if (!empty($authorizationHeader)) {
		return substr($authorizationHeader, 6);
	}

	return null;
}

session_start();

$result = null;
$action = $_GET['action'];
$connector = getConnector();
$documentId = $_GET['documentId'];

$valid = ($action == 'INIT')||($action == 'CREATE') ? true : $connector->validateToken($documentId, getToken());
if ($valid) {
	
	switch ($action) {
		case 'INIT':
			$result = $connector->init($documentId);
		break;
		case 'LOAD':
			$result = $connector->load($documentId);
  	break;
		case 'CREATE':
			$result = $connector->create($_POST['content'], $_POST['properties']);
		break;
		case 'UPDATE':
			$result = $connector->update($documentId, $_GET['revisionNumber']);
		break;
		case 'SAVE':
			$result = $connector->save($documentId, $_POST['patch'], $_POST['properties']);
		break;
	}
	
	if ($result != null) {
		if ($result->getStatus() == 'FORBIDDEN') {
			header('HTTP', true, 403);
		} else {
			header('Content-Type: application/json');
		  echo $result->toJson();
		}
	} else {
		// result is null
		header('HTTP', true, 500);
	}
} else {
	header('HTTP', true, 403);
}
?>