<?php 

require_once dirname( __FILE__ ) . '/result.php';

class CreateResult extends Result {
	
	public function __construct($status, $token, $documentId, $revisionNumber) {
		parent::__construct($status);
		
		$this->token = $token;
		$this->documentId = $documentId;
		$this->revisionNumber = $revisionNumber;
	}
	
	public function toJson() {
		return json_encode(array(
		  'status' => $this->getStatus(),
			'token' => $this->token,
			'documentId' => $this->documentId,
			'revisionNumber' => $this->revisionNumber
		));
	}
	
	private $documentId;
	private $revisionNumber;
	private $token;
}
?>