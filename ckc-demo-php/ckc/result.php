<?php 

class Result {
	
	public function __construct($status) {
		$this->status = $status;
	}

	public function getStatus() {
		return $this->status;
	}

	public function toJson() {
		return json_encode(array(
			'status' => status
		));
	}

	private $status;
}

?>