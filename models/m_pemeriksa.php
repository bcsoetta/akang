<?php

class pemeriksa extends Base_Model {

	// static public member
	const ROLE_PIB = 1;
	const ROLE_CNPIBK = 2;
	const ROLE_CARNET = 4;
	const ROLE_ALL = self::ROLE_PIB|self::ROLE_CNPIBK|self::ROLE_CARNET;

	const STATUS_AVAILABLE = 1;
	const STATUS_BUSY = 2;
	const STATUS_ALL = '1,2';

	const LOKASI_ALL = -1;

	// private member
	private $lastError = '';
	
	public function __construct() {
		$this->load_db();
	}

	public function setLastError($msg) {
		$this->lastError = $msg;
	}

	public function getLastError() {
		return $this->lastError;
	}

	// fungsi ini ngembaliin list pemeriksa aktif/seluruhnya
	public function getListPemeriksa($activeOnly = false) {
		$qstring = "SELECT
						a.id,
						a.fullname
					FROM
						user a
					WHERE
						a.role = 'PEMERIKSA'
						";
		if ($activeOnly) {
			$qstring .= "AND a.active = 'Y'";
		}

		try {
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute(array());


			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$retData = array();

			foreach ($data as $row) {
				$retData[$row['id']] = $row['fullname'];
			}

			return $retData;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());

			return false;
		}

		return false;
	}

	// fungsi ini mengembalikan
	public function getAbsenPemeriksa($tglAbsen) {
		if (!isset($tglAbsen))
			$tglAbsen = date('d/m/Y');

		$qstring = "
			SELECT
				a.id,
				a.fullname,
				IFNULL(b.status, '') status
			FROM
				user a
				LEFT JOIN 
					absensi_pemeriksa b
					ON 
						a.id = b.user_id
						AND b.tgl_absen = STR_TO_DATE(:tglabsen,'%d/%m/%Y')
			WHERE
				a.role = 'PEMERIKSA'
				AND a.active = 'Y';
			";

		try {
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute(array(
				':tglabsen' => $tglAbsen
				));

			if (!$result) {
				throw new PDOException('Kagak tau napa eror bro');
			}

			// good shiet. return some data
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$retData = array(
				'tanggal_absen' => $tglAbsen,
				'pemeriksa' => array()
				);

			foreach ($data as $row) {
				$userid = $row['id'];
				$fullname = $row['fullname'];
				$status = $row['status'];
				// check some shit
				$as_pib = false;
				$as_cnpibk = false;
				$as_carnet = false;

				if (isset($status)) {
					if (strlen($status) > 0) {
						// there's some data
						$stat = explode(',', $status);
						// check array
						if (in_array('PIB', $stat)) {
							$as_pib = true;
						}

						if (in_array('CN_PIBK', $stat)) {
							$as_cnpibk = true;
						}	

						if (in_array('CARNET', $stat)) {
							$as_carnet = true;
						}	
					}
				}

				// append
				$retData['pemeriksa'][] = array(
					'id' => $userid,
					'fullname' => $fullname,
					'as_pib' => $as_pib,
					'as_cnpibk' => $as_cnpibk,
					'as_carnet' => $as_carnet
					);
			}

			return $retData;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());

			return false;
		}

		return false;
	}

	// fungsi ini menginput data absen, atau mengupdatenya klo udh ada
	public function simpanAbsenPemeriksa($tanggal, $pemeriksa) {
		// validasi?
		if (!isset($pemeriksa) || !isset($tanggal)) {
			$this->setLastError("Parameter error untuk simpan absen");
			return false;
		}

		// good to go?
		if (!is_array($pemeriksa)) {
			$this->setLastError("Parameter pemeriksa untuk simpan absen harus bertipe array");
			return false;
		}

		// good
		$qstring = "
			INSERT INTO
				absensi_pemeriksa(
					user_id,
					tgl_absen,
					status
				)
			VALUES
				(
					:userid,
					STR_TO_DATE(:tglabsen, '%d/%m/%Y'),
					:role
				)
			ON DUPLICATE KEY UPDATE
				status = :role2;
			";

		$this->db->beginTransaction();

		try {
			$stmt = $this->db->prepare($qstring);

			// let's execute it then?
			foreach ($pemeriksa as $userid => $role) {
				// var_dump($role);

				$strRole = (is_array($role["role"]) ? implode(',', $role["role"]) : '');
				// echo $strRole;

				$execData = array(
					':userid' => $userid,
					':role' => $strRole,
					':role2' => $strRole,
					':tglabsen' => $tanggal
					);

				$result = $stmt->execute($execData);

				if (!$result) 
					throw new PDOException("Error pas ngabsen pemeriksa #" . $userid);
					
			}

			$this->db->commit();

			return true;
		} catch (PDOException $e) {
			$this->db->rollback();

			$this->setLastError($e->getMessage());
			return false;
		}

		// should not reach here
		return false;
	}

	// fungsi ini ngembaliin status pemeriksa
	public function getStatusPemeriksa() {
		$qstring = "
			SELECT
				a.id,
				a.fullname,
				IFNULL(statpem.status, '') status,
				IFNULL(statpem.lokasi, '') lokasi,
				IFNULL(statpem.status_desc, '') status_desc,
				IFNULL(DATE_FORMAT(statpem.time, '%d/%m/%Y %H:%i'), '') stat_time,
				IFNULL(d.`status`, '') role
			FROM
				user a
				LEFT JOIN
				(
				SELECT
					a.user_id,
					IF(a.`status`='BUSY' AND a.lokasi='BCSH', 'NON-AKTIF', a.status) status,
					IF(a.lokasi='BCSH','',a.lokasi) lokasi,
					a.time,
					CASE
						WHEN a.`status` = 'BUSY' AND a.lokasi = 'BCSH' THEN 'Tidak bertugas'
						WHEN a.`status` = 'AVAILABLE' THEN 'Siap bertugas!'
						ELSE 'Sedang bertugas...'
					END status_desc
				FROM
					status_pemeriksa a
					INNER JOIN
					(
						SELECT
							b.user_id,
							MAX(b.time) most_recent
						FROM
							status_pemeriksa b
						GROUP BY
							b.user_id
					) src 
					ON 
						a.user_id = src.user_id
						AND a.time = src.most_recent
				) statpem
				ON
					a.id = statpem.user_id
				LEFT JOIN
					absensi_pemeriksa d
				ON
					d.user_id = a.id 
					AND d.tgl_absen = DATE(NOW())
			WHERE
				a.role = 'PEMERIKSA'
				AND a.active = 'Y'
			";

		try {
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute();

			// just hope everything's right
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$retData = array();
			foreach ($data as $row) {
				// store it?
				$userid = $row['id'];
				$lokasi = $row['lokasi'];
				$fullname = $row['fullname'];
				$status = $row['status'];
				$status_desc = $row['status_desc'];
				$stat_time = $row['stat_time'];
				$role = $row['role'];

				if (!isset($retData[$userid]))
					$retData[$userid] = array(
						'lokasi' => array(),
						'fullname' => $fullname,
						'status' => $status,
						'status_desc' => $status_desc,
						'stat_time' => $stat_time,
						'role' => $role
						);

				// just append then, and rewrite
				$retData[$userid]['lokasi'][] = $lokasi;
			}

			return $retData;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());

			return false;
		}

		// cannot reach here
		return false;
	}

	// this function returns the difference between two statusData
	function getStatusDifference($arr1, $arr2) {
		// compare each key
		$ret = array();

		foreach ($arr1 as $id => $data) {
			if (isset($arr2[$id])) {
				// if it's totally equal, skip
				if ($arr1[$id]['status'] == $arr2[$id]['status']) {
					// status is equal, waht about location?
					$similar = false;

					$data2 = $arr2[$id];

					// force arr1 to have lokasi
					if (!isset($data['lokasi']))
						$data['lokasi'] = array("");

					if (isset($data['lokasi']) && isset($data2['lokasi'])) {
						if (is_array($data['lokasi']) && is_array($data2['lokasi'])) {
							// compare array
							$diff1 = array_diff($data['lokasi'], $data2['lokasi']);
							$diff2 = array_diff($data2['lokasi'], $data['lokasi']);

							$similar = (count( $diff1 ) == count( $diff2 )) && count($diff1) == 0;
						} else
							$similar = $data['lokasi'] === $data2['lokasi'];	
					}
					
					
					// print_r($data2['lokasi']);

					if ($similar) 
						continue;
				}
			}
			// add
			$ret[$id] = $arr1[$id];
		}

		return $ret;
	}

	// this function breaks statusData into individual query data
	public function buildQueryData($statusData) {
		$queryData = array();

		foreach ($statusData as $userid => $data) {
			// statusnya apaan?
			switch ($data['status']) {
			case 'BUSY':
				// tambah query data per lokasi
				foreach ($data['lokasi'] as $lokasi) {
					$queryData[] = array(
						':id' => $userid,
						':status' => $data['status'],
						':lokasi' => $lokasi
						);
				}
				break;
			case 'NON-AKTIF':
				// status = BUSY, lokasi = BCSH
				$queryData[] = array(
					':id' => $userid,
					':status' => 'BUSY',
					':lokasi' => 'BCSH'
					);
				break;
			case 'AVAILABLE':
				// status = AVAILABLE, lokasi = BCSH
				$queryData[] = array(
					':id' => $userid,
					':status' => 'AVAILABLE',
					':lokasi' => 'BCSH'
					);
				break;
			}
		}

		return $queryData;
	}

	public function simpanStatusPemeriksa($statusData) {
		// build query data first?
		$queryData = $this->buildQueryData($statusData);

		$qstring = "
			INSERT INTO
				status_pemeriksa(
					user_id, 
					status,
					lokasi
				)
			VALUES
				(
					:id,
					:status,
					:lokasi
				)
			";

		// use TRANSACTION!!! IT'S A MUST
		$this->db->beginTransaction();

		try {
			$stmt = $this->db->prepare($qstring);

			// utk tiap query data, eksekusi
			foreach ($queryData as $execData) {
				$result = $stmt->execute($execData);
			}

			// klo sampe sini brati aman
			$this->db->commit();

			return true;
		} catch (PDOException $e) {
			$this->db->rollback();

			$this->setLastError($e->getMessage());
			return false;
		}

		// should not reach here
		return false;
	}

	// DUPLIKAT!! AARRHGHGHGHG
	// fungsi ini mengembalikan list pemeriksa secara detail
	/*
	getPemeriksa
	@role : {ROLE_PIB, ROLE_CNPIBK, ROLE_ALL}
	@status : {STATUS_AVAILABLE, STATUS_BUSY, STATUS_ALL}
	@lokasi : array berisi list lokasi, atau LOKASI_ALL
	*/
	function getPemeriksa($role, $status, $lokasi=self::LOKASI_ALL) {	
		// build list lokasi
		$listLokasi = $lokasi;

		if (is_array($listLokasi)) {
			array_walk($listLokasi, function(&$val) {
				$val = $this->db->quote($val);
			});
			// now flatten it out
			$listLokasi = implode(',', $listLokasi);
		} else if ($listLokasi != self::LOKASI_ALL) {
			// single lokasi, benerin aja
			$listLokasi = $this->db->quote($lokasi);
		}

		// pastiin variable status valid
		// if ($status != self::STATUS_ALL || $status != self::STATUS_BUSY || $status != self::STATUS_AVAILABLE) {
		// 	$status = self::STATUS_ALL;
		// }

		// pastiin variable role valid
		// if ($role != self::ROLE_CNPIBK || $role != self::ROLE_PIB || $role != self::ROLE_ALL) {
		// 	$role = self::ROLE_ALL;
		// }

		// query string parsial
		$qstring = "
			SELECT
				a.id,
				a.fullname,
				IFNULL(statpem.status, '') status,
				IFNULL(statpem.lokasi, '') lokasi,
				IFNULL(statpem.status_desc, '') status_desc,
				IFNULL(DATE_FORMAT(statpem.time, '%d/%m/%Y %H:%i'), '') stat_time,
				IFNULL(d.`status`, '') role
			FROM
				user a
				LEFT JOIN
				(
				SELECT
					a.user_id,
					IF(a.`status`='BUSY' AND a.lokasi='BCSH', 'NON-AKTIF', a.status) status,
					IF(a.lokasi='BCSH','',a.lokasi) lokasi,
					a.status+0 statcode,
					a.time,
					CASE
						WHEN a.`status` = 'BUSY' AND a.lokasi = 'BCSH' THEN 'Tidak bertugas'
						WHEN a.`status` = 'AVAILABLE' THEN 'Siap bertugas!'
						ELSE 'Sedang bertugas...'
					END status_desc
				FROM
					status_pemeriksa a
					INNER JOIN
					(
						SELECT
							b.user_id,
							MAX(b.time) most_recent
						FROM
							status_pemeriksa b
						GROUP BY
							b.user_id
					) src 
					ON 
						a.user_id = src.user_id
						AND a.time = src.most_recent
				) statpem
				ON
					a.id = statpem.user_id
				LEFT JOIN
					absensi_pemeriksa d
				ON
					d.user_id = a.id 
					AND d.tgl_absen = DATE(NOW())
			WHERE
				a.role = 'PEMERIKSA'
				AND a.active = 'Y'
				AND d.status & (:role)	
				AND statpem.statcode IN ($status)
			";

			$q_where_lokasi = "
				AND statpem.lokasi IN ($listLokasi)		
				";

		// build query parameter
		$qparam = array(
			':role' => $role
			);

		if ($lokasi != self::LOKASI_ALL) {
			// $qparam[':listLokasi'] = $listLokasi;
			$qstring .= $q_where_lokasi;
		}

		// eksekusi dah
		try {
			$stmt = $this->db->prepare($qstring);

			// echo $qstring;
			// print_r($qparam);

			$result = $stmt->execute($qparam);

			if (!$result) {
				throw new PDOException($stmt->errorCode());
			}

			// no problem? just go on
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// build return data
			$retData = array(
				'pemeriksa' => array(),
				'gudang' => array()
				);

			foreach ($data as $row) {
				$id = $row['id'];
				$fullname = $row['fullname'];
				$status = $row['status'];
				$lokasi = $row['lokasi'];
				$status_desc = $row['status_desc'];
				$stat_time = $row['stat_time'];
				$role = $row['role'];

				if (!isset($retData['pemeriksa'][$id])) {
					// build new data
					$retData['pemeriksa'][$id] = array(
						'id' => $id,
						'fullname' => $fullname,
						'status' => $status,
						'lokasi' => array(),
						'status_desc' => $status_desc,
						'stat_time' => $stat_time,
						'role' => explode(',', $role)
						);
				}
				// tambah lokasi
				$retData['pemeriksa'][$id]['lokasi'][] = $lokasi;

				// tambah entry gudang
				if (!isset($retData['gudang'][$lokasi])) {
					$retData['gudang'][$lokasi] = array(
						'PIB' => array(),
						'CN_PIBK' => array()
						);
				}

				// tambah pemeriksa di gudang tsb
				if ( in_array('PIB', $retData['pemeriksa'][$id]['role']) )
					$retData['gudang'][$lokasi]['PIB'][] = $id;

				if ( in_array('CN_PIBK', $retData['pemeriksa'][$id]['role']) )
					$retData['gudang'][$lokasi]['CN_PIBK'][] = $id;

				if ( in_array('CARNET', $retData['pemeriksa'][$id]['role']) )
					$retData['gudang'][$lokasi]['CARNET'][] = $id;
			}
			return $retData;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());
			return false;
		}

		return false;
	}

	public function getAbsensi($userid) {
		$qstring = "
			SELECT
				*
			FROM
				absensi_pemeriksa a
			WHERE
				a.user_id = :userid
				AND a.tgl_absen = DATE(NOW())
			";

		try {
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute(array(
				':userid'	=> $userid
				));

			// fetch data
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (count($data) < 1)
				return false;

			// fix data
			$retData = $data[0];

			if ($retData)
				$retData['status'] = explode(',', $retData['status']);
			else
				$retData['status'] = array();

			// return data
			return $retData;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());
		}

		return false;
	}

	// fungsi ini ngembaliin list dokumen lengkap utk diperiksa
	//	$doctype : 'PIB' atau 'CN_PIBK'
	//	$lokasi : array berisi list lokasi ['GDHL', 'APRI']
	//  $status : status terakhir dari dokumen. enum {ON_PROCESS, OVERTIME}
	public function getDokumenOutstanding($doctype, $lokasi, $status) {
		array_walk($lokasi, function(&$v) {
			$v = $this->db->quote($v);
		});

		$listLokasi = implode(',', $lokasi);

		if (!isset($status))
			$status = 'ON_PROCESS';

		$qstring = "
			SELECT
				a.id,
				a.dok_id,
				b.no_dok,
				b.tgl_dok,
				DATE_FORMAT(b.tgl_dok,'%d/%m/%Y') tgl_formatted,
				b.importir,
				b.jml_item,
				b.berat_kg,
				a.`status`,
				a.time,
				a.user_id,
				IF(b.jenis_dok='CN_PIBK', 'CN/PIBK',b.jenis_dok) jenis_dok,
				c.gudang,
				IFNULL(d.real_filename, '') img_filename
			FROM
				status_dok a
			INNER JOIN
				(
				SELECT
					dok_id, 
					MAX(time) recent
				FROM
					status_dok 
				GROUP BY
					dok_id
				) latest
				ON
					a.dok_id = latest.dok_id AND a.time = latest.recent
			INNER JOIN
				batch_detail b
				ON
					a.dok_id = b.id
			INNER JOIN
				batch_header c
				ON
					b.batch_id = c.id
			LEFT JOIN
				pkb_photo d
				ON
					b.photo_id = d.id
			WHERE
				a.`status` = :status
				AND b.jenis_dok = :doctype
				AND c.gudang IN ($listLokasi)
			";

		try {
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute(array(
				':doctype'	=> $doctype,
				':status'	=> $status
				));

			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($data as &$row) {
				if (strlen($row['img_filename']) > 3)
					$row['img_filename'] = base_url($row['img_filename']);
			}

			return $data;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());
		}

		return false;
	}

	public function queryPerforma($dateStart, $dateEnd, $selectedId = -1) {

		// where IN cannot use parameterized style
		if ($selectedId == -1)
			$whereInPemeriksa = "1";
		else
			if (is_array($selectedId)) {
				if (count($selectedId))
					$whereInPemeriksa = "a.id IN (" . implode(',', $selectedId) . ")";
				else {
					$this->setLastError("Pemeriksa kosong");
					return false;
				}
			}

		$qstring = "SELECT
						a.id,
						a.fullname,
						IFNULL(DATE_FORMAT(src.tgl_periksa, '%d/%m/%Y'), '-') tgl_periksa,
						IFNULL(src.tgl_periksa, '-') tgl_periksa_raw,
						IFNULL(src.gudang, '-') gudang,
						IFNULL(src.total_periksa, '-') total_periksa,
						IFNULL(src.jenis_dok, '-') jenis_dok
					FROM
						user a
						LEFT JOIN 
						(
							SELECT
								b.id, b.fullname, DATE(a.time) tgl_periksa, e.gudang, COUNT(*) total_periksa, d.jenis_dok
							FROM
								status_dok a
								JOIN user b
									ON a.user_id = b.id
								JOIN batch_detail d
									ON a.dok_id = d.id
								JOIN batch_header e
									ON d.batch_id = e.id
							WHERE
								a.`status` = 'FINISHED'
								AND DATE(a.time) BETWEEN STR_TO_DATE(:datestart, '%d/%m/%Y') AND STR_TO_DATE(:dateend, '%d/%m/%Y')

							GROUP BY
								a.user_id, 
								DATE(a.time), 
								e.gudang,
								d.jenis_dok
							ORDER BY
								DATE(a.time) DESC
						) src
						ON
							a.id = src.id
					WHERE
						".
						$whereInPemeriksa
						."
					ORDER BY
						a.fullname ASC";

		try {
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute(array(
				':datestart' 	=> $dateStart,
				':dateend'		=> $dateEnd
			));

			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$retData = array();
			foreach ($data as $row) {
				$retData[] = $row;
			}

			return $retData;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());
			return false;
		}
		return false;
	}

	// fungsi ini ngambil seluruh dokumen yg pernah diperiksa oleh pemeriksa
	public function queryRecordPemeriksaan($pemId, $tglPeriksa, $gudang, $doctype) {
		$qstring = "SELECT
						b.no_dok,
						b.tgl_dok,
						b.importir,
						b.jml_item,
						b.berat_kg
					FROM
						status_dok a
						JOIN batch_detail b
							ON a.dok_id = b.id
						JOIN batch_header c
							ON b.batch_id = c.id		
					WHERE
						a.`status` = 'FINISHED'
						AND DATE(a.time) = :tglPeriksa
						AND a.user_id = :pemId
						AND c.gudang = :gudang
						AND b.jenis_dok = :doctype
					GROUP BY
						b.id";

		try {
			$stmt = $this->db->prepare($qstring);

			$stmt->execute(array(
				':tglPeriksa'	=> $tglPeriksa,
				':pemId'		=> $pemId,
				':gudang'		=> $gudang,
				':doctype'		=> $doctype
			));

			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$ret = array();
			foreach ($data as $row) {
				$ret[] = $row;
			}

			return $ret;
		} catch (PDOException $e) {
			
		}

		return false;
	}
}
?>