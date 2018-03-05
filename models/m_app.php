<?php
/*
	Model : app
	berisi segala fitur dalam aplikasi SAPI
*/
define('DOC_PIB', 1);
define('DOC_CNPIBK', 2);

class app extends Base_Model {
	private $error = '';

	private $appTitle = 'AKANG - BC Soetta';
	
	function __construct() {
		parent::__construct();

		$this->load_db();
	}

	function getLastError() {
		return $this->error;
	}

	function setLastError($msg) {
		$this->error = $msg;
	}

	function getTitle() {
		return $this->appTitle;
	}

	function dbtrInsertRequestHeader($uploader_id, $gudang) {
		// sanitize input
		$gudang = htmlentities(trim($gudang));
		$uploader_id = htmlentities(trim($uploader_id));

		if (strlen($gudang) < 3
			|| strlen($uploader_id) < 1) {

			$this->setLastError("App Error: Data header korup. Mau coba-coba ya?");
			return false;
		}

		$q_insert_header = "
			INSERT INTO
				batch_header(uploader_id, gudang)
			VALUES
				(:uploader_id, :gudang)
			";

		$stmt_insert_header = $this->db->prepare($q_insert_header);

		$result = $stmt_insert_header->execute(array(
			':uploader_id' 	=> $uploader_id,
			':gudang'		=> $gudang
			));

		// is it successful? there's hope
		if ($result) {
			$headerId = $this->db->lastInsertId();
			return $headerId;
		}

		return false;
	}

	// Brief documentation
	//	This function insert a detailed request into database, parameters are...
	//	@doctype	: 'PIB' or 'CN/PIBK'
	//	@no_dok		: nomor PIB ato CN/PIBK
	//	@tgl_dok 	: tanggal dokumen, format 'dd/mm/yyyy'
	//	@header_id	: batch id atas detail ini
	//	@img_id 	: id foto barang atas detail ini

	function dbtrPrepareDetailInsert() {
		$q_insert_detail = "
			INSERT INTO
				batch_detail(
					jenis_dok,
					no_dok,
					tgl_dok,
					importir,

					batch_id,
					photo_id,
					tahun_dok,
					
					jml_item,
					berat_kg
				)
			VALUES
				(
					:jenis_dok,
					:no_dok,
					STR_TO_DATE(:tgl_dok, '%d/%m/%Y'),
					:importir,
					
					:header_id,
					:img_id,
					YEAR(STR_TO_DATE(:tgl_dok2, '%d/%m/%Y')),

					:jml_item,
					:berat_kg
				)";

		$stmt_insert_detail = $this->db->prepare($q_insert_detail);

		return $stmt_insert_detail;
	}

	function dbtrInsertDetail($stmt, $header_id, $doctype, $no_dok, $tgl_dok, $importir, $jml_item, $berat_kg, $img_id) {
		// sanitize heavily
		if (strlen($doctype) < 3
			|| strlen($header_id) < 1
			|| strlen($no_dok) < 1
			|| strlen($tgl_dok) < 10
			|| strlen($importir) < 4 
			) {

			$this->setLastError("App Error: data detail tidak lengkap. Cek lagi utk no dok: " . $no_dok);
			return false;
		}
			

		$result = $stmt->execute(array(
			':jenis_dok' 	=> $doctype,
			':no_dok'		=> $no_dok,
			':tgl_dok'		=> $tgl_dok,
			':importir'		=> $importir,
			':header_id'	=> $header_id,
			':img_id'		=> $img_id,
			':tgl_dok2'		=> $tgl_dok,
			':jml_item'		=> $jml_item,
			':berat_kg'		=> $berat_kg
			));

		return $result;
	}

	// make helper function to create form
	function submitRequest($uploader_id, $gudang, $doctype, $no_dok, $tgl_dok, $importir, $jml_item, $berat_kg, $img_name = null, $img_src = null) {
		// check sanitation
		if (!is_array($no_dok) || !is_array($tgl_dok) || !is_array($importir)) {
			$this->setLastError("Obviously you must send data in correct format!");
			return false;
		}

		// make sure they're all equal
		if (count($no_dok) != count($tgl_dok) || count($no_dok) != count($importir)) {
			$this->setLastError("Request data is not matching in count. Check yer brain.");
			// the array size are not matching!!
			return false;
		}

		// make sure it's something
		if (count($no_dok) < 1) {
			$this->setLastError("User sent an empty request!!");
			return false;
		}

		// instantiate image model
		$image = new image;

		// list of inserted image so far
		$imageInserted = array();

		// pastiin gk ada yg dobel...
		if (isset($img_name)) {
			$i = 0;

			for ($i=0; $i<count($img_name); $i++) {
				$imgname = htmlentities(trim($img_name[$i]));
				$imgsrc =$img_src[$i];

				$fullName = image::$imgFolder . $image->getDatedFilename($imgname);

				if (file_exists($fullName)) {
					$this->setLastError("Gambar [" . $img_name[$i] . "] udh pernah diupload!");
					return false;
				}
			}
		}

		$this->db->beginTransaction();

		try {

			$header_id = $this->dbtrInsertRequestHeader($uploader_id, $gudang);

			// gotta be careful here
			if (!$header_id) {
				throw new PDOException("Error on Header! => " . $this->getLastError());
				// $this->db->rollback();
			}

			// got header, next step

			// prepared statements
			$stmt_img = $image->dbtrPrepareImageInsert();
			$stmt_detail = $this->dbtrPrepareDetailInsert();
			
			// loop over each element in succession
			$i = 0;
			$limit = count($no_dok);

			
			while ($i < $limit) {
				
				// now we gotta sanitize shits
				$no_dok_cln = htmlentities(trim($no_dok[$i]));
				$tgl_dok_cln = htmlentities(trim($tgl_dok[$i]));				// it's better be trimmed only
				$importir_cln = htmlentities(trim($importir[$i]));

				$jml_item_cln = $jml_item[$i];
				$berat_kg_cln = $berat_kg[$i];

				$img_id = null;

				// only do it if we have image sent
				if (isset($img_name)) {
					$img_name_cln = htmlentities(trim($img_name[$i]));
					// insert image, then detail
					$complete_name = $image->getDatedFilename($img_name_cln);

					// inserted image record...
					$imageInserted[] = image::$imgFolder . $complete_name;

					$img_id = $image->dbtrInsertImage($stmt_img, $image->getDatedFilename($img_name_cln), $img_src[$i]);

					if (!$img_id) {
						// if image exists, delete it
						throw new PDOException("Error on Image for dok ".$no_dok_cln." => " . $image->getLastError());
						// $this->db->rollback();
					}
				}


				// got img id, insert detail
				$result = $this->dbtrInsertDetail($stmt_detail, $header_id, $doctype, $no_dok_cln, $tgl_dok_cln, $importir_cln, $jml_item_cln, $berat_kg_cln, $img_id);

				// wait, we failed? fuck that shit
				if (!$result) {
					throw new PDOException("Error on Detail of Request! => " . $this->getLastError());
					// $this->db->rollback();
				}

				++$i;
			}

			$this->db->commit();

			return $header_id;
		} catch (PDOException $e) {
			$this->db->rollback();

			$msg = $e->getMessage();

			// list gambar yg udh terlanjur keupload 
			foreach ($imageInserted as $img) {
				// $msg .= "\r\n" . $img;
				unlink($img);
			}

			$this->setLastError($msg);

			return false;
		}

		return false;
	}

	function getBatchData($batch_id) {
		if (!isset($batch_id))
			return false;

		// sanitize the fuck outta this shit
		$batch_id = (int) htmlentities(trim($batch_id));

		if (!is_numeric($batch_id))
			return false;

		// two query, to get header and detail
		$q_header = "
				SELECT
					a.id,
					a.time_uploaded,
					DATE_FORMAT(a.time_uploaded,'%d/%m/%Y %H:%i') time_formatted,
					a.gudang,
					a.uploader_id,
					b.fullname,
					c.jenis_dok doctype
				FROM
					batch_header a
					INNER JOIN user b ON a.uploader_id = b.id
					LEFT JOIN batch_detail c ON a.id = c.batch_id
				WHERE
					a.id = :batch_id
				GROUP BY
					c.batch_id;
			";

		$q_detail = "
				SELECT
					a.id,
					a.jenis_dok,
					a.no_dok,
					a.tgl_dok,
					DATE_FORMAT(a.tgl_dok,'%d/%m/%Y') tgl_dok_formatted,
					a.importir,
					a.jml_item,
					a.berat_kg,
					b.filename,
					b.real_filename,
					latest_status.status,
					latest_status.time,
					DATE_FORMAT(latest_status.time, '%d/%m/%Y %H:%i') time_formatted
				FROM
					batch_detail a
					LEFT JOIN pkb_photo b ON a.photo_id = b.id
					LEFT JOIN
					(
					SELECT
						d.dok_id,
						d.`status`,
						d.time
					FROM
						status_dok d
						INNER JOIN
						(
						SELECT
							dok_id,
							MAX(time) recent
						FROM
							status_dok e
						GROUP BY
							dok_id
						) latest
						ON
							d.dok_id = latest.dok_id
							AND d.time = latest.recent
					) latest_status
					ON 
						a.id = latest_status.dok_id
				WHERE
					a.batch_id = :batch_id
			";

		$this->db->beginTransaction();

		try {
			// query for header
			$stmt_header = $this->db->prepare($q_header);

			$res1 = $stmt_header->execute(array(
				':batch_id' => $batch_id
				));

			// error when querying
			if (!$res1)
				throw new PDOException("Error query header data! batch_id: " . $batch_id);

			$rows = $stmt_header->fetchAll(PDO::FETCH_ASSOC);

			// no data returned
			if (count($rows) < 1) 
				throw new PDOException("No header found with id: " + $batch_id);

			// set return data
			$retData = array(
				'batch_id' => $rows[0]['id'],
				'upload_time' => $rows[0]['time_formatted'],
				'gudang' => $rows[0]['gudang'],
				'uploader' => $rows[0]['fullname'],
				'doctype' => $rows[0]['doctype'],
				'data' => array()
				);

			// query for detail
			$stmt_detail = $this->db->prepare($q_detail);

			$res2 = $stmt_detail->execute(array(
				':batch_id' => $batch_id
				));

			// error when querying
			if (!$res2)
				throw new PDOException("Error querying detail data! batch_id: " . $batch_id);

			// grab detail, add to return data
			$rows = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);

			foreach ($rows as $row) {
				// add to return data
				$retData['data'][] = $row;
			}

			$this->db->commit();

			return $retData;
		} catch (PDOException $e) {
			$this->db->rollback();

			$this->setLastError($e->getMessage());

			return false;
		}

		return false;
	}

	function testGarbage($value) {
		$stmt = $this->db->prepare(
			"INSERT INTO garbage VALUES (:id)"
			);
		return $stmt->execute(array(
			':id' => $value
			));
	}

	// this function query request headers
	// param is an array containing all necessary parameters,
	//	=> datestart, dateend, paramtype, paramvalue, doctype, pageid, itemperpage
	function queryRequest($param) {
		$q_main_body = "
			SELECT
				a.id,
				a.time_uploaded,
				a.gudang,
				b.fullname,
				DATE_FORMAT(a.time_uploaded, '%d/%m/%Y %H:%i') time_formatted,
				c.jenis_dok,
				COUNT(*) total_dok,
				(
				SELECT
					COUNT(DISTINCT(aa.dok_id)) total
				FROM
					status_dok aa
				INNER JOIN
					batch_detail bb
					ON
						aa.dok_id = bb.id
				WHERE
					aa.`status` = 'FINISHED'
					AND bb.batch_id = a.id
				) total_finished
			FROM
				batch_header a
				INNER JOIN user b ON a.uploader_id = b.id
				LEFT JOIN batch_detail c ON a.id = c.batch_id
			WHERE
				1
			";

		// doctype {PIB, CN_PIBK, *}
		$q_where_jenis_dok = "
				AND c.jenis_dok = :doctype
			";

		$q_where_date_range = "
				AND DATE(a.time_uploaded) BETWEEN STR_TO_DATE(:datestart, '%d/%m/%Y') AND STR_TO_DATE(:dateend, '%d/%m/%Y')
			";

		$q_where_batchid = "
				AND a.id = :batchid
			";

		$q_where_lokasi = "
				AND a.gudang LIKE :lokasi
			";

		$q_where_uploader = "
				AND b.fullname LIKE :uploader
			";

		$q_where_forceid = "
				AND a.uploader_id = :forceid
			";

		// startid = (pageid-1) * itemperpage
		$q_tail = "
			GROUP BY
				c.batch_id
			ORDER BY
				a.time_uploaded DESC
			LIMIT
				:startid, :itemperpage
			";

		$q_total_body = "
			SELECT
				COUNT(DISTINCT(a.id)) total
			FROM
				batch_header a
				INNER JOIN user b ON a.uploader_id = b.id
				LEFT JOIN batch_detail c ON a.id = c.batch_id
			WHERE
				1
			";

		// collect parameter. essentials go first
		$qparam = array(
			':datestart'	=> $param['datestart'],
			':dateend' 	=> $param['dateend'],
			':startid'	=> max( array( ($param['pageid']-1) * $param['itemperpage'], 0 ) ),
			':itemperpage' => $param['itemperpage']
			);

		$qtotalparam = array(
			':datestart'	=> $param['datestart'],
			':dateend'		=> $param['dateend']
			);

		// build query string and query parameter
		$qstring = $q_main_body . $q_where_date_range;
		$qtotal = $q_total_body . $q_where_date_range;

		// use parameter type?
		if (isset($param['paramtype']) && isset($param['paramvalue'])) {
			if (strlen($param['paramvalue'])) {
				switch ($param['paramtype']) {
				case 'batchid':
					$qstring .= $q_where_batchid;
					$qtotal .= $q_where_batchid;

					$qparam[':batchid'] = $param['paramvalue'];
					$qtotalparam[':batchid'] = $param['paramvalue'];
					break;
				case 'lokasi':
					$qstring .= $q_where_lokasi;
					$qtotal .= $q_where_lokasi;

					$qparam[':lokasi'] = $param['paramvalue'];
					$qtotalparam[':lokasi'] = $param['paramvalue'];
					break;
				case 'uploader':
					// add %
					$param['paramvalue'] = '%' . $param['paramvalue'] . '%';

					$qstring .= $q_where_uploader;
					$qtotal .= $q_where_uploader;

					$qparam[':uploader'] = $param['paramvalue'];
					$qtotalparam[':uploader'] = $param['paramvalue'];
					break;
				}
			}
			
		}

		if ( in_array(strtoupper($param['doctype']), array('PIB', 'CN_PIBK')) ) {
			// use the parameter
			$qstring .= $q_where_jenis_dok;
			$qtotal .= $q_where_jenis_dok;

			$qparam[':doctype'] = $param['doctype'];
			$qtotalparam[':doctype'] = $param['doctype'];
		}

		// force it?
		if (isset($param['forceid'])) {
			if (strlen($param['forceid'])) {
				// add it
				$qstring .= $q_where_forceid;
				$qtotal .= $q_where_forceid;

				$qparam[':forceid'] = $param['forceid'];
				$qtotalparam[':forceid'] = $param['forceid'];
			}
		}

		$qstring .= $q_tail;

		// echo $qstring;
		
		try {
			// there is data, so let's build up shits
			$retData = array(
				'pageid' => $param['pageid'],
				'itemperpage' => $param['itemperpage'],
				'totaldata' => 0,	// calculate later
				'totalpage' => 0,	// calculate later
				'data'	=> null
				);

			// now query total data
			$stmt2 = $this->db->prepare($qtotal);

			$result = $stmt2->execute($qtotalparam);

			if (!$result) {
				throw new PDOException("Error calculating total data..." . $stmt2->errorCode());
			}

			// everything's fine, grab it
			$data = $stmt2->fetchAll(PDO::FETCH_ASSOC);

			if (!count($data)) {
				throw new PDOException("Total query result in empty data...weird. ");
			}

			$total = $data[0]['total'];

			$retData['totaldata'] = $total;
			$retData['totalpage'] = ceil($total/$retData['itemperpage']);

			// correct the shit (user might navigate too far, e.g. Page ID is out of boundary)
			if ($retData['totalpage'] > 0) {
				if ($param['pageid'] < 1)
					$param['pageid'] = 1;

				if ($param['pageid'] > $retData['totalpage'])
					$param['pageid'] = $retData['totalpage'];

				$retData['pageid'] = $param['pageid'];

				// correct parameter
				$param['itemperpage'] = max(1, $param['itemperpage']);
				$qparam[':startid'] = max( array( ($param['pageid']-1) * $param['itemperpage'], 0 ) );
			} else 
				return null;	// no data
			

			// now query the real data
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute($qparam);

			if (!$result) {
				throw new PDOException("Error querying for request data... ".$stmt->errorCode());
			}

			// everything's fine. Grab data
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (!count($data))
				return null;	// no data

			$retData['data'] = $data;

			// gotta append something
			foreach ($retData['data'] as &$row) {
				$row['url'] = base_url('app/viewbatch/'.$row['id']);
			}

			return $retData;
			
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());

			return false;
		}		

		$this->setLastError("Must be god. You should not have reached here.");
		return false;
	}

	// this function returns active warehouse
	function getActiveWarehouse() {
		$q_active = "
			SELECT
				DISTINCT(a.gudang) gudang
			FROM
				batch_header a
			";

		try {
			$stmt = $this->db->prepare($q_active);

			$result = $stmt->execute();

			if (!$result) {
				throw new PDOException("Error fetching list of active warehouse...".$stmt->errorCode());
			}

			// welp, it's good
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$retData = array();

			foreach ($rows as $row) {
				$retData[] = $row['gudang'];
			}

			return $retData;

		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());

			return false;
		} catch (Exception $e) {
			$this->setLastError($e->getMessage());

			return false;
		}

		return false;
	}

	// this function queries the statistics for each warehouse
	function queryOutstanding($gudang) {
		$q_head = "
			SELECT
				src.gudang,
				src.jenis_dok,
				COUNT(*) total,
				src.status,
				MIN(src.tgl_dok) oldest,
				MAX(src.tgl_dok) newest,
				
				DATEDIFF(MIN(src.tgl_dok), '1900-01-01') oldest_serial,
				DATEDIFF(MAX(src.tgl_dok), '1900-01-01') newest_serial,
				
				DATE_FORMAT(MIN(src.tgl_dok), '%d/%m/%Y') oldest_formatted,
				DATE_FORMAT(MAX(src.tgl_dok), '%d/%m/%Y') newest_formatted,
				
				DATEDIFF(DATE(NOW()), MIN(src.tgl_dok)) oldest_age,
				DATEDIFF(DATE(NOW()), MAX(src.tgl_dok)) newest_age,
				
				IFNULL(pem_aktif.total, 0) pemeriksa_aktif

			FROM
			(
			SELECT
				a.*,
				(
					SELECT b.status FROM status_dok b WHERE b.dok_id = a.id ORDER BY b.`time` DESC LIMIT 1
				) status,
				b.gudang
			FROM
				batch_detail a
				JOIN batch_header b ON a.batch_id = b.id
			WHERE
			"
			;
			$q_tail = "
			) src
				LEFT JOIN (
				
			
					SELECT
						a.lokasi,
						'PIB' role,
						COUNT(*) total
					FROM
						status_pemeriksa a
					INNER JOIN
					(
						SELECT
							user_id,
							MAX(time) most_recent
						FROM
							status_pemeriksa
						GROUP BY
							user_id
					) recent
					ON 
						a.user_id = recent.user_id
						AND a.time = recent.most_recent
					INNER JOIN
					(
						SELECT
							user_id
						FROM
							absensi_pemeriksa
						WHERE
							tgl_absen = DATE(NOW())
							AND FIND_IN_SET('PIB', status)
					) absen
					ON
						a.user_id = absen.user_id
					WHERE
						a.`status` = 'BUSY'
					GROUP BY
						a.lokasi
						
						
					UNION
					
					
					SELECT
						a.lokasi,
						'CN_PIBK' role,
						COUNT(*) total
					FROM
						status_pemeriksa a
					INNER JOIN
					(
						SELECT
							user_id,
							MAX(time) most_recent
						FROM
							status_pemeriksa
						GROUP BY
							user_id
					) recent
					ON 
						a.user_id = recent.user_id
						AND a.time = recent.most_recent
					INNER JOIN
					(
						SELECT
							user_id
						FROM
							absensi_pemeriksa
						WHERE
							tgl_absen = DATE(NOW())
							AND FIND_IN_SET('CN_PIBK', status)
					) absen
					ON
						a.user_id = absen.user_id
					WHERE
						a.`status` = 'BUSY'
					GROUP BY
						a.lokasi
			
				
				
				) pem_aktif 
				ON 
					src.gudang = pem_aktif.lokasi
					AND src.jenis_dok = pem_aktif.role
			GROUP BY
				src.gudang,
				src.jenis_dok,
				src.status
			";
		

		$q_criteria = "MATCH(b.gudang) AGAINST(:keywords)";
		$q_criteria_def = "1";

		

		// build our query
		$param = array();
		$q_string = '';

		if (!isset($gudang))
			$q_string = $q_head . $q_criteria_def . $q_tail;
		else {
			// flattened our keywords
			if (is_array($gudang))
				$keywords = trim(implode(' ', $gudang));
			else
				$keywords = trim($gudang);

			$param[':keywords'] = $keywords;

			$q_string = $q_head . $q_criteria . $q_tail;
		}

		// start query
		try {
			$stmt = $this->db->prepare($q_string);

			$result = $stmt->execute($param);

			if (!$result) {
				throw new PDOException('Error querying statistics.. ' . $stmt->errorCode());
			}

			// yaay, let's do shit
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// now let's build our nested array
			$data = array();

			foreach ($rows as $row) {
				if (!isset($data[$row['gudang']]))
					$data[$row['gudang']] = array();

				$data[ $row['gudang'] ][$row['jenis_dok']][$row['status']] = array(
						'total' => $row['total'],
						'newest' => $row['newest'],
						'oldest' => $row['oldest'],
						'newest_serial' => $row['newest_serial'],
						'oldest_serial' => $row['oldest_serial'],
						'newest_formatted' => $row['newest_formatted'],
						'oldest_formatted' => $row['oldest_formatted'],
						'newest_age' => $row['newest_age'],
						'oldest_age' => $row['oldest_age']
					);

				if (!isset($data[ $row['gudang'] ][$row['jenis_dok']]['pemeriksa_aktif']))
					$data[ $row['gudang'] ][$row['jenis_dok']]['pemeriksa_aktif'] = $row['pemeriksa_aktif'];
				
			}

			// calculate summary data?
			return $data;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());

			return false;
		}

		return false;
	}

	// fungsi ini ngeflag dokumen
	//	$userid : id user yg ngeflag
	//	$flag : flag {'FINISHED', 'CANCELED', 'ON_PROCESS'}
	//	$ids : array berisi id dokumen yg mau diflag
	public function flagDokumen($userid, $flag, $ids) {
		$qstring = "
			INSERT INTO
				status_dok(
					dok_id,
					status,
					user_id
				)
			VALUES
				(
					:docid,
					:flag,
					:userid
				)
			";

		$this->db->beginTransaction();

		try {
			$stmt = $this->db->prepare($qstring);

			foreach ($ids as $docid) {
				$param = array(
					':docid' => $docid,
					':flag' => $flag,
					':userid' => $userid
					);

				$result = $stmt->execute($param);
			}

			// no error here, good
			$this->db->commit();

			return true;
		} catch (Exception $e) {
			$this->db->rollback();
			$this->setLastError($e->getMessage());

			return false;
		}

		return false;
	}
}
?>