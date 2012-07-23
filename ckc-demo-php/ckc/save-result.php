<?php 

require_once dirname( __FILE__ ) . '/result.php';

class SaveResult extends Result {

	public function __construct($status, $revisionNumber) {
		parent::__construct($status);

		$this->revisionNumber = $revisionNumber;
	}

	public function toJson() {
		return json_encode(array(
				'status' => $this->getStatus(),
				'revisionNumber' => $this->revisionNumber
		));
	}

	private $revisionNumber;
}
?>