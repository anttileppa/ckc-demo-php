<?php

require_once dirname( __FILE__ ) . '/../../ckc-connector.php';
require_once dirname( __FILE__ ) . '/db.php';
require_once dirname( __FILE__ ) . '/diff_match_patch.php';

class DemoConnector implements iCKCConnector {
	
	private function parseProperties($properties) {
		$result = array();
		$i = 0;
		$l = strlen($properties);
		$escaped = false;
		$buffer = "";
		$name = null;
	
		while ($i < $l) {
			$c = $properties{$i};
			if ($c == '\\') {
				$escaped = true;
			} else {
				if ($escaped) {
					$buffer .= $c;
				} else {
					switch ($c) {
						case ':':
							$name = $buffer;
							$buffer = "";
							break;
						case ';':
							$result[$name] = $buffer;
							$buffer = "";
							break;
						default:
							$buffer .= $c;
							break;
					}
				}
				$escaped = false;
			}
	
			$i++;
		}
	
		$result[$name] = $buffer;
	
		return $result;
	}

	public function init($documentId) {
		$token = uniqid();
		$_SESSION['ckc-token-' . $documentId] = $token;
		return new InitResult(iCKCConnector::STATUS_OK, $token);
	}

	public function validateToken($documentId, $token) {
		//$sessionToken = $_SESSION['ckc-token-' . $documentId];
		//return $sessionToken == $token;
		return true;
	}

	public function create($content, $properties) {
		// Document is created elsewhere in this example
	}
	
	private function getRevisionProperties($mysqli, $revisionId) {
		$properites = array();
		
		if ($result = $mysqli->query(sprintf("SELECT (select name from Property where id = property_id) as name, value FROM RevisionProperty WHERE revision_id = %d", $revisionId))) {
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$properites[] = array(
				  name => $row['name'],
					value => $row['value']
				);
			}
		
			$result->close();
		}
		
		return $properites;
	}

	public function update($documentId, $revisionNumber) {
		$mysqli = db_connect();
		
		$revisions = array();
			
	  if ($result = $mysqli->query(sprintf("SELECT id, number, patch from Revision where document_id = %d and number > %d", $documentId, $revisionNumber))) {
	  	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	  		$revisionId = $row['id'];
	  		$number = $row['number'];
	  		$patch = $row['patch'];
	  		$revisions[] = array(
  				'number' => $number,
  				'patch' => $patch,
  				'properties' => $this->getRevisionProperties($mysqli, $revisionId)
	  		);
	  	}

	  	$result->close();
		}
		
		db_close($mysqli);
		
		return new UpdateResult(iCKCConnector::STATUS_OK, $revisions);
	}
	
	private function getDocumentProperties($mysqli, $documentId) {
		$properites = array();
	
		if ($result = $mysqli->query(sprintf("SELECT (select name from Property where id = property_id) as name, value FROM DocumentProperty WHERE document_id = %d", $documentId))) {
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$properites[] = array(
						name => $row['name'],
						value => $row['value']
				);
			}
	
			$result->close();
		}
	
		return $properites;
	}

	public function load($documentId) {
		$mysqli = db_connect();
		
		$query = sprintf("SELECT data, revisionNumber from Document where id = %d", $documentId);
		
		if ($result = $mysqli->query($query)) {
			$result_object = $result->fetch_object();
		  $revisionNumber = $result_object->revisionNumber;
		  $data = $result_object->data;
		  $result->close();
		}

		$result = new LoadResult(iCKCConnector::STATUS_OK, $revisionNumber, $data, $this->getDocumentProperties($mysqli, $documentId));
		
		db_close($mysqli);
		
		return $result;
	}

	public function save($documentId, $patch, $properties) {
		$status = iCKCConnector::STATUS_OK;
		$revisionNumber = null;
		$diffMatchPatch = new diff_match_patch();
		
		$mysqli = db_connect();
		$mysqli->autocommit(false);
		try {
			$documentData = null;
			$revisionData = null;
			$revisionNumber = null;
			
			if ($result = $mysqli->query(sprintf("SELECT max(revisionNumber) + 1 as nextRevision from Document where id = %d", $documentId))) {
				$result_object = $result->fetch_object();
				$revisionNumber = $result_object->nextRevision;
				$result->close();
			}
			
			if (!empty($patch)) {
				$query = sprintf("SELECT data from Document where id = %d", $documentId);
				if ($result = $mysqli->query($query)) {
					$result_object = $result->fetch_object();
					$oldData = $result_object->data;
					$result->close();
					
					$patches = $diffMatchPatch->patch_fromText($patch);
					$patchResult = $diffMatchPatch->patch_apply($patches, $oldData);
					foreach ($patchResult[1] as $applied) {
						if ($applied == false) {
							$status = iCKCConnector::STATUS_CONFLICT;
							break;
						}
					}
					
					if ($status != iCKCConnector::STATUS_CONFLICT) {
						$patchedText = $patchResult[0];
						if ($patchedText != $oldData) {
						  $diffs = $diffMatchPatch->diff_main($oldData, $patchedText);
						  $diffMatchPatch->diff_cleanupEfficiency($diffs);
						  $revisionData = $diffMatchPatch->patch_toText($diffMatchPatch->patch_make($diffs));
						  $documentData = $patchedText;
						}
					}
				}
			}	
			
			if ($status != iCKCConnector::STATUS_CONFLICT) {
				$parsedProperties = $this->parseProperties($properties);
				foreach ($parsedProperties as $name => $value) {
					$propertyId = null;
					$propertyExists = false;
					
				  if ($result = $mysqli->query(sprintf("SELECT id FROM Property where name = '%s'", mysql_escape_string($name)))) {
				  	$result_object = $result->fetch_object();
				  	$propertyId = $result_object->id;
				  	$propertyExists = !empty($propertyId);
				  	$result->close();
				  	$mayExist = true;
				  } 
				  
				  if ($propertyExists == false) {
				  	$mysqli->query(sprintf("INSERT INTO Property (name) values ('%s')", mysql_escape_string($name)));
				  	$propertyId = $mysqli->insert_id;
				  	$mayExist = false;
				  }
				  
				  $insertNew = true;
				  if ($propertyExists == true) {
					  if ($result = $mysqli->query(sprintf("SELECT id FROM DocumentProperty where document_id = %d and property_id = %d", $documentId, $propertyId))) {
					  	$result_object = $result->fetch_object();
					  	$documentPropertyId = $result_object->id;
					  	$insertNew = empty($documentPropertyId);
					  	if ($insertNew == false)
					  	  $mysqli->query(sprintf("UPDATE DocumentProperty set value = '%s' where id = %d", mysql_escape_string($value), $documentPropertyId));
					  }
				  }
				  
				  if ($insertNew == true) {
				  	$mysqli->query(sprintf("INSERT INTO DocumentProperty (document_id, property_id, value) values (%d, %d, '%s')", $documentId, $propertyId, mysql_escape_string($value), $documentPropertyId));
				  }
				}
				
				$mysqli->query(sprintf("INSERT INTO Revision (document_id, number, patch) values (%d, %d, '%s')", $documentId, $revisionNumber, mysql_escape_string($revisionData)));
				$revisionId = $mysqli->insert_id;
				
				$mysqli->query(sprintf("INSERT INTO RevisionProperty (revision_id, property_id, value) values (%d, %d, '%s')", $revisionId, $propertyId, mysql_escape_string($value), $documentPropertyId));

				if (!empty($documentData)) {
				  $mysqli->query(sprintf("UPDATE Document set data = '%s', revisionNumber = %d where id = %d", mysql_escape_string($documentData), $revisionNumber, $documentId));
				}
				
				$mysqli->commit();
			} else {
				$mysqli->rollback();
			}
		} catch (Exception $e) {
			$status = iCKCConnector::STATUS_UNKNOWN_ERROR;
			$mysqli->rollback();
		} 
		
		db_close($mysqli);
		
		return new SaveResult($status, $revisionNumber);
	}
	
}

register_ckc_connector("demo", new DemoConnector());

?>