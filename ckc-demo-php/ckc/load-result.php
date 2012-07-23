<?php 

require_once dirname( __FILE__ ) . '/result.php';

class LoadResult extends Result {
	
  public function __construct($status, $revisionNumber, $content, $properties) {
		parent::__construct($status);
	
		$this->revisionNumber = $revisionNumber;
		$this->content = $content;
		$this->properties = $properties;
	}
	
	public function toJson() {
		return json_encode(array(
				'status' => $this->getStatus(),
				'revisionNumber' => $this->revisionNumber,
				'content' => $this->content,
				'properties' => $this->properties
		));
	}
	
	private $revisionNumber;
	private $content;
	private $properties;
}
?>