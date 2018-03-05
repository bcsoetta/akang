<?php
/*
	handles all shit pertaining to spreadsheet files
*/

include_once "libraries/phpexcel/Classes/PHPExcel/IOFactory.php";

class excel extends Base_Model {
	function __construct() {
		parent::__construct();
	}

	// this function reads all available cell data 
	// in first sheet, returning them as array and filetype
	function readRawData($filename) {
		if (!file_exists($filename))
			return false;

		$fileType = PHPExcel_IOFactory::identify($filename);
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objExcel = $objReader->load($filename);

		$data = array(1, $objExcel->getActiveSheet()->toArray(null, true, false, true));

		if ($data[0] != 1)
			return false;

		return array(
			"type" => $fileType,
			"data" => $data[1]
			);
	}

	function calcExcelDate($src) {
		$UNIX_DATE = ($src - 25569) * 86400;

		return gmdate("d/m/Y", $UNIX_DATE);
	}

	function parsePIBRequest($data, $fileType = null) {
		if (!isset($data))
			return false;

		if (!$data)
			return false;

		// skip the first row, maybe?
		$retData = array();

		$refCol = array();

		foreach ($data as $row) {
			if (isset($row['A'])) {
				if ( preg_match("/no/i", trim(strtolower($row['A'])) ) == 1) {
					// setup the reference column

					foreach ($row as $key => $value) {
						$refCol[$key] = $value;
					}

					// skip the line
					continue;
				}
				
			}
			// add it
			$rowData = array();

			$firstCheck = true;
			$valid = true;

			foreach ($row as $key => $value) {
				$rowData[$refCol[$key]] = $value;

				if ($firstCheck) {
					$firstCheck = false;
					if (strlen(trim($value)) < 1)
						$valid = false;
				}
			}

			// if invalid, skip
			if (!$valid)
				continue;

			// fix the date
			if (isset($fileType)) {
				if ($fileType == 'CSV') {
					// needs to match pattern
				} else {
					// xls and xlsx are simpler, just calculate date
					$rowData['tgl_dok'] = $this->calcExcelDate($rowData['tgl_dok']);
				}
			}

			$retData[] = $rowData;
		}

		return $retData;
	}
}
?>