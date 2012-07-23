<?php

require_once dirname( __FILE__ ) . '/create-result.php';
require_once dirname( __FILE__ ) . '/init-result.php';
require_once dirname( __FILE__ ) . '/load-result.php';
require_once dirname( __FILE__ ) . '/save-result.php';
require_once dirname( __FILE__ ) . '/update-result.php';

interface iCKCConnector {

	/**
	 * Initializes session.
	 *
	 * Besides preparing the document, implementing method should at least provide token for future authorization.
	 */
	public function init($documentId);

	/**
	 * Validates that access token is valid.
	 */
	public function validateToken($documentId, $token);

	/**
	 * Creates new document. Method is called on the first save of the document if no documentId was provided
	 */
	public function create($content, $properties);

	/**
	 * Returns updates for the document since the revisionNumber
	 */
	public function update($documentId, $revisionNumber);

	/**
	 * Loads latest revision of the document.
	 */
	public function load($documentId);

	/**
	 * Saves changes to the document.
	 */
	public function save($documentId, $patch, $properties);
	
	const STATUS_OK = "OK";
	const STATUS_FORBIDDEN = "FORBIDDEN";
	const STATUS_CONFLICT = "CONFLICT";
	const STATUS_UNKNOWN_ERROR = "UNKNOWN_ERROR";
}

?>