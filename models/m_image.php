<?php
/*
	Model : app
	berisi segala fitur dalam aplikasi SAPI
*/

class image extends Base_Model {
	public static $imgFolder = 'assets/img/upload/';

	// private $lastErr = '';

	function __construct() {
		parent::__construct();
	}

	// function setLastError($msg) {
	// 	$this->lastErr = $msg;
	// }

	// function getLastError() {
	// 	return $this->lastErr;
	// }

	function saveBase64Image($filename, $base64Data) {
		// sanitize
		if (strlen($filename) < 3 || strlen($base64Data) < 8) {
			$this->setLastError("Image error: image data is missing!");
			return false;
		}

		// check for shit
		if (!isset($filename) || !isset($base64Data)) {
			$this->setLastError("Image error: user is too stupid.");
			return false;
		}

		// explode the data, and do sanitation
		$data = explode(',', $base64Data);

		// var_dump($data);

		if (count($data) < 2) {
			$this->setLastError("Image error: data is not consistent. count is: " . count($data) . " and it's ". $data[0]);
			return false;
		}

		// first part contains MIME
		$match = preg_match('%^data:image\/(png|jpg|jpeg)\;base64$%i', $data[0], $matches);

		// it's not a valid MIME
		if (!$match) {
			$this->setLastError("Image error: invalid MIME encountered.");
			return false;
		}

		// second part is the real data
		$realData = base64_decode($data[1]);

		// now save it
		$fullFilename = self::$imgFolder . $filename;

		// make sure it does not exist
		if (file_exists($fullFilename)) {
			$this->setLastError("Image error: image[" . $fullFilename . "] already existed.");
			return false;
		}

		if (file_put_contents($fullFilename, $realData)) {
			// success, let's give user full info
			return array(
				'filename' => $filename,
				'fullFilename' => $fullFilename,
				'hash' => md5_file($fullFilename)
				);
		}

		$this->setLastError("Image error: failed to write image");

		return false;
	}

	function dbtrPrepareImageInsert() {
		$this->load_db();
		// db query
		$q_insert = "
			INSERT INTO
				pkb_photo(`filename`, `real_filename`, `hash`)
			VALUES
				(:filename, :real_filename, :hash)
			";

		// insert entry
		$stmt_insert = $this->db->prepare($q_insert);

		return $stmt_insert;
	}

	function dbtrInsertImage($stmt, $filename, $base64Data) {
		if (!isset($this->db))
			$this->load_db();

		$result = $this->saveBase64Image($filename, $base64Data);

		if (is_array($result)) {			
			// insert entry
			$res1 = $stmt->execute(array(
				':filename' => $result['filename'],
				':real_filename' => $result['fullFilename'],
				':hash' => $result['hash']
				));

			return $this->db->lastInsertId();
		}

		// if reach here, delete shit
		if (file_exists($filename))
			unlink($filename);

		return false;
	}

	function getDatedFilename($filename) {
		return date("Ymd").'_'.$filename;
	}

	// this function saves the base64 encoded image, and store it into database
	// then it returns the id of the db row containing info about the image
	function saveWithDBEntry($filename, $base64Data) {
		
		$result = $this->saveBase64Image($filename, $base64Data);

		if (is_array($result)) {
			// gotta save entry to database
			$this->load_db();

			// db query
			$q_insert = "
				INSERT INTO
					pkb_photo(`filename`, `real_filename`, `hash`)
				VALUES
					(:filename, :real_filename, :hash)
				";

			$q_get_id = "
				SELECT
					LAST_INSERT_ID() id
				FROM
					pkb_photo
				";

			$this->db->beginTransaction();

			try {
				// insert entry
				$stmt_insert = $this->db->prepare($q_insert);

				$res1 = $stmt_insert->execute(array(
					':filename' => $result['filename'],
					':real_filename' => $result['fullFilename'],
					':hash' => $result['hash']
					));

				if (!$res1) {
					// if fail, don't bother
					$this->db->rollback();
					// delet this
					unlink($result['fullFilename']);
					return null;
				}

				// grab last inserted id
				$stmt_get_id = $this->db->prepare($q_get_id);

				$res2 = $stmt_get_id->execute();

				$rows = $stmt_get_id->fetchAll(PDO::FETCH_ASSOC);

				// make sure it passes through
				if (count($rows) < 1) {
					$this->db->rollback();
					// delet this
					unlink($result['fullFilename']);
					return null;
				}

				$id = $rows[0]['id'];

				$this->db->commit();

				return $id;
			} catch (PDOException $e) {
				$this->db->rollback();

				// delet this
				unlink($result['fullFilename']);

				return null;
			}
		}

		return null;	// null or 0 is fine tho
	}
}
?>