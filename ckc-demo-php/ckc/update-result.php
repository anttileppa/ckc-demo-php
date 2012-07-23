<?php

require_once dirname( __FILE__ ) . '/result.php';

class UpdateResult extends Result {

	public function __construct($status, $revisions) {
		parent::__construct($status);

		$this->revisions = $revisions;
	}

	public function toJson() {
		return json_encode(array(
				'status' => $this->getStatus(),
				'revisions' => $this->revisions
		));
	}

	private $revisions;
}
?>