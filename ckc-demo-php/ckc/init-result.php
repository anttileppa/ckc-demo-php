<?php 

require_once dirname( __FILE__ ) . '/result.php';

class InitResult extends Result {

	public function __construct($status, $token) {
		parent::__construct($status);
	
		$this->token = $token;
	}
	
	public function toJson() {
		return json_encode(array(
				'status' => $this->getStatus(),
				'token' => $this->token
		));
	}
	
	private $token;
	
}
?>