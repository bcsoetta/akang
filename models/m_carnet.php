<?php
/*
	Model: carnet
	berisi fungsi2 pembantu utk manipulasi data carnet
*/

class carnet extends Base_Model{
	function __construct(){
		parent::__construct();
		$this->load_db();	//we load database
		if(!isset($_SESSION))
			session_start();
		//for search form
		if(!isset($_SESSION['search'])){
			//build session default values
			$this->reset_search_config();
		}
		//for image upload
		if(!isset($_SESSION['imgupload'])){
			$_SESSION['imgupload']=array();
		}
		//TODO: session for input data
	}

	//clear semua data simpanan
	function clear_data(){
		unset($_SESSION['search']);
		$this->clear_upload_cache();
	}

	//reset variabel pencarian
	function reset_search_config(){
		$_SESSION['search']=array(
				'active'=>false,
				'datestart'=>'01/01/'.date('Y'),
				'dateend'=>date('d/m/Y'),
				'paramtype'=>'agenda',
				'status'=>'all',
				'paramvalue'=>'',
				'pageid'=>1,
				'pagecount'=>-1
				);
	}

	//ambil semua variabel pencarian
	function get_search_config($name=null){
		if($name==null)
			return $_SESSION['search'];
		if(isset($_SESSION['search'][$name]))
			return $_SESSION['search'][$name];
		return null;
	}

	//nyimpen variabel pencarian
	function save_search_config($key, $value){
		$changed = false;
		if($_SESSION['search'][$key] != $value)
			$changed = true;
		$_SESSION['search'][$key] = $value;
		return $changed;
	}

	//input data carnet
	//TODO : VALIDASI!!!
	//TODO : SIMPAN DATA DALAM SESSION, KALO GAGAL BALIKIN KE USER
	//TODO : KLO LOLOS, CLEAR!!
	//TODO : TRANSACTION : a) input carnet b) link gambar ke carnet c) delete gambar sampah
	//return : true = sukses, false = gagal
	function add_carnet($type, $no, $date, $holder, $expire, $jenis_peng, $ren_tgl_tutup, $ren_lok_tutup, $lokasi, $imgIdList=null){
		//should validate here first
		$qinsert = "INSERT INTO tb_carnet(no_agenda, tahun, cnt_type, cnt_no, cnt_date, cnt_holder, cnt_expire, jenis_peng, ren_tgl_tutup, ren_lok_tutup, lokasi, wkt_rekam) VALUES(getSequence('AGENDA', YEAR(NOW())), YEAR(NOW()), :type, :no, STR_TO_DATE(:date, '%d/%m/%Y'), :holder, STR_TO_DATE(:expire, '%d/%m/%Y'), :jenis_peng, STR_TO_DATE(:ren_tgl_tutup, '%d/%m/%Y'), :ren_lok_tutup, :lokasi, NOW());";
		$qlastdata = "SELECT no_agenda, tahun FROM tb_carnet WHERE id = LAST_INSERT_ID();";

		//build list
		$imgCount = 0;
		if(isset($imgIdList) && !is_null($imgIdList)){
			$imgCount = count($imgIdList);
			$imgList = array();
			foreach($imgIdList as $v){
				$imgList[] = "'$v'";
			}
			$imgList = implode(',', $imgList);
			$qupdate = "UPDATE tb_img SET no_agenda = :agenda, tahun = :tahun WHERE id IN ($imgList);";
		}
		
		try{
			$stmt = $this->db->prepare($qinsert);
			$stmt2 = $this->db->prepare($qlastdata);

			if($imgCount)
				$stmt3 = $this->db->prepare($qupdate);
			//mulai transaksi
			$this->db->beginTransaction();

			//stmt : insert data
			$res = $stmt->execute(array(
				'type'=>$type,
				'no'=>$no,
				'date'=>$date,
				'holder'=>$holder,
				'expire'=>$expire,
				'jenis_peng'=>$jenis_peng,
				'ren_tgl_tutup'=>$ren_tgl_tutup,
				'ren_lok_tutup'=>$ren_lok_tutup,
				'lokasi'=>$lokasi
				));

			//stmt2 : ambil data terakhir
			$res2 = $stmt2->execute();
			if($res2){
				$data = $stmt2->fetchAll(PDO::FETCH_ASSOC);
				$data = $data[0];
				//grab data
				if($imgCount){
					$res3 = $stmt3->execute(array(
						'agenda'=>$data['no_agenda'],
						'tahun'=>$data['tahun']
						));
				}
			}else{
				echo $e->getMessage();
				$this->db->rollBack();
				return false;
			}

			$this->db->commit();
		}catch(PDOException $e){
			$this->db->rollBack();
			return false;
		}
		//apus image cache
		$this->clear_upload_cache();

		return true;
	}

	//edit carnet
	function edit_carnet($id, $type, $no, $date, $holder, $expire, $jenis_peng, $ren_tgl_tutup, $ren_lok_tutup, $lokasi, $imgIdList=null){
		//should validate here first
		$qupdatecarnet = "UPDATE tb_carnet SET cnt_no=:cnt_no, cnt_date=STR_TO_DATE(:cnt_date, '%d/%m/%Y'), cnt_holder=:cnt_holder, cnt_type=:cnt_type, cnt_expire=STR_TO_DATE(:cnt_expire, '%d/%m/%Y'), jenis_peng=:jenis_peng, ren_tgl_tutup=STR_TO_DATE(:ren_tgl_tutup, '%d/%m/%Y'), ren_lok_tutup=:ren_lok_tutup, lokasi=:lokasi WHERE id=:id";

		$imgCount = 0;
		if(!is_null($imgIdList)){
			$imgCount = count($imgIdList);
			$imgList = array();
			foreach($imgIdList as $v){
				$imgList[] = "'$v'";
			}
			$imgList = implode(',', $imgList);
			$qupdateimg = "UPDATE tb_img INNER JOIN tb_carnet ON tb_carnet.id = :id SET tb_img.no_agenda = tb_carnet.no_agenda, tb_img.tahun = tb_carnet.tahun WHERE tb_img.id IN ($imgList)";
		}
		
		$update_data=array(
			'id'=>$id,
			'cnt_no'=>$no,
			'cnt_date'=>$date,
			'cnt_holder'=>$holder,
			'cnt_type'=>$type,
			'cnt_expire'=>$expire,
			'jenis_peng'=>$jenis_peng,
			'ren_tgl_tutup'=>$ren_tgl_tutup,
			'ren_lok_tutup'=>$ren_lok_tutup,
			'lokasi'=>$lokasi
			);
		//do the thing
		try{
			$stmt1 = $this->db->prepare($qupdatecarnet);
			if($imgCount)
				$stmt2 = $this->db->prepare($qupdateimg);

			//use transaction
			$this->db->beginTransaction();

			$stmt1->execute($update_data);

			if($imgCount)
				$stmt2->execute(array('id'=>$id));

			$this->db->commit();
		}catch(PDOException $e){
			$this->db->rollBack();
			return false;
		}
		//apus image cache
		$this->clear_upload_cache();
		return true;
	}

	//hapus carnet
	function delete_carnet($id){
		$ret=false;

		//pertama, ambil list gambar
		//kedua, transaksi --> hapus entri dari db
		//ketiga apus gambar
		//ketiga commit

		//1. list gambar
		$qimg = "SELECT tb_carnet.id AS cnt_id, tb_img.id AS img_id, tb_img.filename FROM tb_carnet	LEFT JOIN tb_img ON tb_carnet.no_agenda = tb_img.no_agenda AND tb_carnet.tahun = tb_img.tahun	WHERE tb_carnet.id =:id AND tb_img.filename IS NOT NULL";
		$qdel = "DELETE tb_carnet, tb_img FROM tb_carnet LEFT JOIN tb_img	ON tb_carnet.no_agenda = tb_img.no_agenda AND tb_carnet.tahun = tb_img.tahun WHERE tb_carnet.id =:id";
		$data = array('id'=>$id);

		$fname = array();

		//multi - prepare
		try{
			$stmt = $this->db->prepare($qimg);
			$stmt2 = $this->db->prepare($qdel);
			//echo "prepare---ok<br>";
		}catch(PDOException $e){
			//echo "prepare---fail<br>";
			return false;
		}

		try{
			$this->db->beginTransaction();

			//ambil list
			$ret |= $stmt->execute($data);
			
			if($ret){
				//dapet data. coba apus filenya
				$rootDir = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME);
				$imgList = $stmt->fetchAll(PDO::FETCH_ASSOC);
				//echo "jumlah gambar --- ".count($imgList);
				foreach($imgList as $img){
					$fname = $rootDir.'/assets/img/upload/'.$img['filename'];
					if(file_exists($fname) && is_file($fname))
						unlink($fname);
				}
			}
			
			$ret |= $stmt2->execute($data);

			$this->db->commit();
			//echo "trans---ok<br>";
		}catch(PDOException $e){
			$this->db->rollBack();
			//echo "trans---fail<br>";
			return false;
		}
		
		return $ret;
	}

	//tutup pengajuan carnet dengan catatan
	function close_carnet($id, $catatan, &$error=null){
		$qstring = "INSERT INTO tb_closing (no_agenda, tahun, catatan, wkt_rekam) SELECT no_agenda, tahun, :catatan, NOW() FROM tb_carnet WHERE tb_carnet.id = :id";

		try{
			$stmt = $this->db->prepare($qstring);
			$stmt->execute(array(
				'catatan'=>$catatan,
				'id'=>$id
				));
		}catch(PDOException $e){
			if(!is_null($error))
				$error = $e->getMessage();
			return false;
		}
		return true;
	}

	//query data berdasarkan konfigurasi pencarian
	function query_data($count, $search, &$paging = null){
		if($count==null)
			$count=10;	//10 latest record
		//sanitize pageid
		$pageid = max(1, min($search['pageid'], $search['pagecount']));
		$offset = $count * ($pageid-1);
		//query data
		$qdata = array();
		//build query + kriteria
		$kriteria = "1";
		$daterange = "1";
		$status = "1";
		//build criteria
		if(isset($search['paramtype']) && isset($search['paramvalue'])){
			$search['paramvalue'] = trim($search['paramvalue']);
			if(strlen($search['paramvalue']) && strlen($search['paramtype'])){
				//kriteria valid
				switch($search['paramtype']){
					case "carnetno":
						$kriteria = "cnt_no LIKE :searchparam ";
						break;
					case "agenda":
						$kriteria = "tb_carnet.no_agenda LIKE :searchparam ";
						break;
					case "holder":
						$kriteria = "cnt_holder LIKE :searchparam ";
						break;
				}
				//fill in query data
				$qdata['searchparam'] = '%'.$search['paramvalue'].'%';
			}
		}

		//status query
		if($search['status']=='open'){
			$status = "tb_closing.id IS NULL";
		}else if($search['status']=='closed'){
			$status = "tb_closing.id IS NOT NULL";
		}

		//build date range
		if(isset($search['datestart']) && isset($search['dateend'])){
			$daterange = "cnt_date BETWEEN STR_TO_DATE(:datestart, '%d/%m/%Y') AND STR_TO_DATE(:dateend, '%d/%m/%Y')";

			$qdata['datestart'] = $search['datestart'];
			$qdata['dateend'] = $search['dateend'];
		}

		$qdata['count'] = $count;
		$qdata['offset'] = $offset;

		$qstring = "SELECT tb_carnet.id, CONCAT('050100-', lpad(tb_carnet.no_agenda, 5,'0'),'/',cnt_type,'/',lokasi,'/',tb_carnet.tahun) AS agenda, cnt_no, DATE_FORMAT(cnt_date, '%d %b %Y') AS tgl_carnet, cnt_holder, cnt_type, DATE_FORMAT(cnt_expire, '%d %b %Y') AS expire_carnet,
					CASE 
						WHEN jenis_peng='IRE' THEN 'Impor-Reekspor'
						WHEN jenis_peng='ERI' THEN 'Ekspor-Reimpor'
					END AS pengajuan, lokasi,
					CASE
						WHEN tb_closing.id IS NULL THEN 'Open'
						ELSE 'Closed'
					END AS status FROM `tb_carnet` LEFT JOIN tb_closing ON tb_carnet.no_agenda=tb_closing.no_agenda AND tb_carnet.tahun=tb_closing.tahun 
					WHERE ".$kriteria." AND ".$daterange." AND ".$status." ORDER BY tb_carnet.wkt_rekam DESC LIMIT :count OFFSET :offset";
		$stmt = $this->db->prepare($qstring);
		$ret = $stmt->execute($qdata);

		/*echo $qstring;
		print_r($qdata);*/

		$ret_data = null;
		if($ret){
			$ret_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		//get paging info
		if(isset($paging)){
			$qstring = "SELECT COUNT(tb_carnet.id) AS total, CEIL(COUNT(tb_carnet.id)/:count) AS total_page FROM tb_carnet LEFT JOIN tb_closing ON tb_carnet.no_agenda=tb_closing.no_agenda AND tb_carnet.tahun=tb_closing.tahun WHERE ".$kriteria." AND ".$daterange." AND ".$status;
			$stmt2 = $this->db->prepare($qstring);

			unset($qdata['offset']);

			$ret = $stmt2->execute($qdata);
			if($ret){
				$ret = $stmt2->fetchAll(PDO::FETCH_ASSOC);
				$paging = $ret[0];
				//fix halaman
				$paging['pageid'] = max(1, min($paging['total_page'], $search['pageid']) );
				$paging['count'] = $count;

				$this->save_search_config('pageid', $paging['pageid']);
				$this->save_search_config('pagecount', $paging['count']);
			}
		}
		//kembalikan data hasil kueri
		return $ret_data;
	}

	//hapus cache dari gambar hasil upload
	function clear_upload_cache(){
		unset($_SESSION['imgupload']);
	}

	//ambil data upload gambar
	function get_upload_cache(){
		return $_SESSION['imgupload'];
	}

	//tambah cache gambar
	function add_upload_cache($data){
		if(count($data) > 1 || is_array($data))
			$_SESSION['imgupload'] = array_merge($_SESSION['imgupload'], $data);
		else{
			$_SESSION['imgupload'][]=$data;
		}
	}

	//set cache gambar
	function set_upload_cache($data){
		$_SESSION['imgupload']=$data;
	}

	//nanganin upload gambar
	//mindain gambar ke folder upload, sekaligus
	//buat entri database tb_img
	function handle_upload($cached=true){
		$ret_val = array();
		$ret_msg = array();

		//query string
		$qstring = "INSERT INTO tb_img(filename, orig_name) VALUES(:filename, :origname );";
		$stmt = $this->db->prepare($qstring);

		//some default settings
		$accepted=array(
			'image/png',
			'image/jpeg'
			);

		$savepath = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME)."/assets/img/upload";
		//files
		if(!isset($_FILES['file']))
			return;
		for($i=0; $i<count($_FILES['file']['name']); $i++){
			//mime-type check
			if(!in_array($_FILES['file']['type'][$i], $accepted) || $_FILES['file']['error'][$i]){
				//log error
				if($_FILES['file']['error'][$i])
					$ret_msg[] = '['.$_FILES['file']['name'][$i].']'." Failed to upload. Reason Code(".$_FILES['file']['error'][$i].")";
				else if(!in_array($_FILES['file']['type'][$i], $accepted) )
					$ret_msg[] = '['.$_FILES['file']['name'][$i].']'." has unacceptable extension!";
				continue;
			}
			$ext = pathinfo($_FILES['file']['name'][$i], PATHINFO_EXTENSION);
			$d = new Datetime();
			$ts = $d->getTimeStamp();

			$newname = $ts.'-'.$i.'.'.$ext;
			$tmpname = $_FILES['file']['tmp_name'][$i];

			//connect db and move
			//move it
			if(move_uploaded_file($tmpname, "$savepath/$newname")){
				//echo $tmpname.' : '."$savepath/$newname\n";
				//execute statement
				$ret=$stmt->execute(array(
					'filename'=>$newname,
					'origname'=>$_FILES['file']['name'][$i]
					));
				if($ret){
					//sukses, get last insert id
					$imgId = $this->db->lastInsertId();
					//append
					$ret_val[] = array(
						'id'=>$imgId,
						'filename'=>base_url('assets/img/upload/'.$newname),
						'downlink'=>base_url('app/carnet/saveimg/'.$imgId),
						'name'=>$_FILES['file']['name'][$i]
						);
				}else{
					//gagal entry gambar, apus di mari
					//ENTAR AJEHHH!!
					$ret_msg[] = 'Something error shiiiiit';
				}
			}
		}

		//input ke cache
		if($cached)
			$this->add_upload_cache($ret_val);
		return array('file'=>$ret_val, 'msg'=>$ret_msg);
	}

	//ambil data carnet seutuhnya
	function get_carnet_data($id){
		$qstring = "SELECT tb_carnet.*, tb_closing.catatan, CONCAT('050100-', lpad(tb_carnet.no_agenda, 5,'0'),'/',cnt_type,'/',lokasi,'/',tb_carnet.tahun) AS agenda,
					CASE 
						WHEN jenis_peng = 'IRE' THEN 'Impor-Reekspor'
						WHEN jenis_peng = 'ERI' THEN 'Ekspor-Reimpor'
						ELSE '???'
					END AS pengajuan,
					DATE_FORMAT(cnt_date, '%d/%m/%Y') AS tgl_carnet,
					DATE_FORMAT(cnt_expire, '%d/%m/%Y') AS tgl_kadaluarsa,
					DATE_FORMAT(ren_tgl_tutup, '%d/%m/%Y') AS tgl_tutup,
					CASE 
						WHEN tb_closing.catatan IS NULL THEN 'Open'
						ELSE 'Closed'
					END AS status
					FROM tb_carnet LEFT JOIN tb_closing ON tb_carnet.no_agenda=tb_closing.no_agenda AND tb_carnet.tahun=tb_closing.tahun WHERE tb_carnet.id=:id";
		$data=null;
		try{
			$stmt = $this->db->prepare($qstring);
			$ret = $stmt->execute(array('id'=>$id));
			if($ret){
				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if(count($rows))
					$data=$rows[0];
			}
		}catch(PDOException $e){
			return false;
		}
		return $data;
	}

	function get_carnet_images($agenda, $tahun){
		$qstring = "SELECT * FROM tb_img WHERE no_agenda = :agenda AND tahun = :tahun";
		try{
			$stmt = $this->db->prepare($qstring);
			$ret = $stmt->execute(array('agenda'=>$agenda, 'tahun'=>$tahun));
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}catch(PDOException $e){
			echo $e->getMessage();
		}
		return null;
	}

	function delete_carnet_image($id, &$stat = null){
		//hapus entri dari database
		//hapus entri dari upload cache + file fisik
		//kembalikan true apabila salah satunya benar
		$ret = false;
		$rootDir = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME);
		//STEP 1. Database + FISIK
		$qsearch = "SELECT filename FROM tb_img WHERE id=:id";
		$qdelete = "DELETE FROM tb_img WHERE id=:id";

		$fileList = array();
		try{
			$stmt = $this->db->prepare($qsearch);
			$stmt2 = $this->db->prepare($qdelete);

			$this->db->beginTransaction();

			$ret = $stmt->execute( array('id'=>$id) );
			//apabila ada data, coba apus datanya
			if($ret){
				//grab data
				$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

				//the fullname
				if(count($data)){
					$fullName = $rootDir.'/'.'assets/img/upload/'.$data[0]['filename'];
					//attempt deletion and just don't give a fuck
					//unlink($fullName);
					$fileList[] = $fullName;
				}
			}
			//good, now simply delete em from db (reversible up to now)
			$ret = $ret | $stmt2->execute( array('id'=>$id) );

			$this->db->commit();
		}catch(PDOException $e){
			if(isset($stat) && !is_null($stat)){
				$stat = $e->getMessage();
			}
			$this->db->rollBack();
			return false;
		}

		//apus dari cache
		if(isset($_SESSION['imgupload'])){
			$found=-1;
			for($i=0; $i<count($_SESSION['imgupload']); $i++){
				if($_SESSION['imgupload'][$i]['id']==$id){
					$found = $i;
					break;
				}
			}
			//is it found?
			if($found >=0 ){
				//remove it
				array_splice($_SESSION['imgupload'], $found, 1);
			}
		}

		//apus betulan
		if(count($fileList)){
			try{
				if(file_exists($fileList[0]) && is_file($fileList[0]))
					unlink($fileList[0]);
			}catch(Exception $e){
				$stat.="\n".$e->getMessage();
			}
		}

		return true;
	}

	function cetak_lembar_kontrol($id){
		$libpath = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME).'/libraries';
		set_include_path($libpath);
		require('fpdf/fpdf.php');

		$data = $this->get_carnet_data($id);

		//output pdf berisi data lembar kontrol
		$noagenda = $data['agenda'];
		$nocarnet = $data['cnt_no'];
		$nmcarnet = $data['cnt_holder'];
		$reexim = $data['tgl_tutup'];
		$lokasi = $data['lokasi'];
		$jenis = '['.$data['cnt_type'].']';

		$kop1 = " LEMBAR KONTROL CARNET                                                                         LEMBAR KONTROL CARNET "; 

	    $pdf = new FPDF('L','mm','A4');
	//	$pdf->SetTopMargin(5);
	    $pdf->AddPage();

	    #kop Lembar Kontrol
	    $pdf->SetFont('Arial','B','12');
		$pdf->SetX(45);
	    $pdf->Cell(8,2, $kop1, '0', 3, 'L');
		
		
		#menggambar form lembar kontrol
		$pdf->Rect(6, 13, 140, 172); //kotak Pertama
		$pdf->Rect(150, 13, 140, 172); //kotak Kedua
		
		$pdf->Rect(27, 19, 111, 0.1); //garis no agenda
		$pdf->Rect(53, 24, 85, 0.1); //garis no carnet		
		$pdf->Rect(41, 29, 97, 0.1); //garis nama carnet holder
		$pdf->Rect(51, 34, 25,0.1); //garis rencana ekspor
			$pdf->Rect(93, 34, 45,0.1); //garis lokasi
		
		$pdf->Rect(172, 19, 111, 0.1);   //garis no agenda 2 
		$pdf->Rect(198, 24, 85, 0.1); //garis no carnet 2
		$pdf->Rect(186, 29, 97, 0.1); //garis nama carnet holder 2
		$pdf->Rect(196, 34, 25,0.1); //garis rencana ekspor 2
			$pdf->Rect(237, 34, 46,0.1); //garis lokasi 2
		
		//KOTAK KIRI
		$pdf->Rect(6, 38, 140, 10); //Nama Tabel 1
			$pdf->Rect(15,38,0.1,147); //garis v 1
			$pdf->Rect(63,38,0.1,60); // garis v 2		
			$pdf->Rect(78,38,0.1,60); // garis v 3
				$pdf->Rect(78,42.5,34,0.1); // garis h5
				$pdf->Rect(95,42.5,0.1,55.5);	// garis v4		
			$pdf->Rect(112,38,0.1,60); // garis v5
			$pdf->Rect(126,38,0.1,60); // garis v6
		
		//KOTAK KANAN
		$pdf->Rect(150, 38, 140, 10); //Nama Tabel 2
			$pdf->Rect(159,38,0.1,147);	//garis v1
			$pdf->Rect(207,38,0.1,60); //garis v2
			$pdf->Rect(222,38,0.1,60); //garis v2
				$pdf->Rect(222,42.5,34,0.1); // garis v3
				$pdf->Rect(239,42.5,0.1,55.5); //garis v4
			$pdf->Rect(256,38,0.1,60); //garis v5
			$pdf->Rect(270,38,0.1,60); //garis v6
			
		//KOTAK SEKSI KIRI
		$pdf->Rect(6,65,140,16); //kotak seksi
		$pdf->Rect(15, 98, 131,0.1); //garis horizontal bawah kasubsi
		
		//KOTAK SEKSI KANAN
		$pdf->Rect(150,65,140,16); //kotak seksi
		$pdf->Rect(159, 98, 131,0.1); //garis horizontal bawah kasubsi
		
		//HORIZONTAL PEMERIKSA KIRI
		$pdf->Rect(74, 116, 65,0.1);//garis horizontal pemeriksa yang ditunjuk
		
		//HORIZONTAL PEMERIKSA KANAN
		$pdf->Rect(218, 116, 65,0.1);//garis horizontal pemeriksa yang ditunjuk
			
		//KOTAK 4 KIRI
		$pdf->Rect(6,120,140,10); // kotak 4 pemeriksa fisik
			$pdf->Rect(63,120,0.1,10);	//v2	
			$pdf->Rect(78,120,0.1,10); //v3
			$pdf->Rect(95,120,0.1,10); //v4
			$pdf->Rect(112,120,0.1,10); //v5
			$pdf->Rect(126,120,0.1,10); //v6
				
		//KOTAK 4 KANAN
		$pdf->Rect(150,120,140,10); // kotak 4 pemeriksa fisik	
			$pdf->Rect(207,120,0.1,10);	//v2	
			$pdf->Rect(222,120,0.1,10); //v3
			$pdf->Rect(239,120,0.1,10); //v4
			$pdf->Rect(256,120,0.1,10); //v5
			$pdf->Rect(270,120,0.1,10); //v6
				
		//KOTAK 5 KIRI
		$pdf->Rect(6,156,140,15); // kotak 5 Kasubsi HPC			
				$pdf->Rect(65,156,0.1,29);		
				$pdf->Rect(79,156,0.1,29);
				$pdf->Rect(95,156,0.1,29);
				$pdf->Rect(112,156,0.1,29);
				$pdf->Rect(126,156,0.1,29);
		
		//KOTAK 5 KANAN
			$pdf->Rect(150,156,140,15); // kotak 5 Kasubsi HPC			
				$pdf->Rect(209,156,0.1,29);		
				$pdf->Rect(224,156,0.1,29);
				$pdf->Rect(240,156,0.1,29);
				$pdf->Rect(256,156,0.1,29);
				$pdf->Rect(270,156,0.1,29);
		
		#TAMPILAN DATA YANG DIPANGGIL KIRI
		$pdf->SetFont('Arial','','9');
		$pdf->SetXY(28,15);  $pdf->Cell(0,3.5, $noagenda, '0', 1, 'L');	
		$pdf->SetXY(54,20.2);  $pdf->Cell(0,3.5, $nocarnet, '0', 1, 'L');	
	    $pdf->SetXY(45,25.3);  $pdf->Cell(0,3.5, $nmcarnet, '0', 1, 'L');
		$pdf->SetXY(52,30.5);  $pdf->Cell(0,3.5, $reexim, '0', 1, 'L');
		$pdf->SetXY(93,30.5);  $pdf->Cell(0,3.5, $lokasi, '0', 1, 'L');
		
		#TAMPILAN DATA YANG DIPANGGIL KANAN
		$pdf->SetFont('Arial','','9');
		$pdf->SetXY(172,15);  $pdf->Cell(0,3.5, $noagenda, '0', 1, 'L');	
		$pdf->SetXY(198,20.2);  $pdf->Cell(0,3.5, $nocarnet, '0', 1, 'L');	
	    $pdf->SetXY(189,25.3);  $pdf->Cell(0,3.5, $nmcarnet, '0', 1, 'L');
		$pdf->SetXY(197,30.5);  $pdf->Cell(0,3.5, $reexim, '0', 1, 'L');
		$pdf->SetXY(238,30.5);  $pdf->Cell(0,3.5, $lokasi, '0', 1, 'L');
		
		
		#tampilkan fixed text form lembar kontrol 
		//KIRI
	    $pdf->SetFont('Arial','','9');
		$pdf->SetXY(7,16);	$pdf->Cell(0,3.5, 'No.Agenda:', '0', 0, 'L');
		$pdf->SetXY(7,21); $pdf->Cell(0,3.5, 'Carnet: '.$jenis.' No.Carnet:', '0', 0, 'L');		
		$pdf->SetXY(7,26);	$pdf->Cell(0,3.5, 'Nama Carnet Holder:', '0', 0, 'L');			
		$pdf->SetXY(7,31); $pdf->Cell(0,3.5, 'Rencana Ekspor:Tgl/bln/thn', '0', 0, 'L');	
			$pdf->SetXY(80, 31); $pdf->Cell(0,3.5, 'Lokasi:', '0', 0, 'L');
		
		$pdf->SetXY(7,41);	$pdf->Cell(0,3.5, 'No.', '0', 0, 'L');
		$pdf->SetXY(32,41);	$pdf->Cell(0,3.5, 'Kegiatan', '0', 0, 'L'); //kegiatan
		$pdf->SetXY(63,41);	$pdf->Cell(0,3.5, 'Tanggal', '0', 0, 'L'); //tanggal
		$pdf->SetXY(91,39);	$pdf->Cell(0,3.5, 'Jam', '0', 0, 'L'); //jam
		$pdf->SetXY(79,44);	$pdf->Cell(0,3.5, 'Diterima', '0', 0, 'L'); //diterima
		$pdf->SetXY(97,44);	$pdf->Cell(0,3.5, 'Selesai', '0', 0, 'L'); //selesai
		$pdf->SetXY(114,41);$pdf->Cell(0,3.5, 'Paraf', '0', 0, 'L'); //paraf
		$pdf->SetXY(130,41);$pdf->Cell(0,3.5, 'Nama', '0', 0, 'L'); //nama
		
		$pdf->SetXY(7,49);	$pdf->Cell(0,3.5, '1.', '0', 0, 'L');//no 1
		$pdf->SetXY(16,49);	$pdf->Cell(0,3.5, 'Pelaksana Administrasi:', '0', 0, 'L');//Pelaksana adm
		$pdf->SetXY(16,54);	$pdf->Cell(0,3.5, 'Penerimaan dan penelitian', '0', 0, 'L');//penerimaan dan penelitian
		$pdf->SetXY(16,58);	$pdf->Cell(0,3.5, 'kelengkapan dokumen', '0', 0, 'L');
		
		$pdf->SetXY(7,66);	$pdf->Cell(0,3.5, '2.', '0', 0, 'L');//no 2
		$pdf->SetXY(16,66);	$pdf->Cell(0,3.5, 'Kepala Seksi PKC:', '0', 0, 'L');//Kepala Seksi HPC
		$pdf->SetXY(16,71);	$pdf->Cell(0,3.5, 'Penunjukkan Kasubsi HPC', '0', 0, 'L');//penunjukan
		
		$pdf->SetXY(7,82);	$pdf->Cell(0,3.5, '3.', '0', 0, 'L');//no 3
		$pdf->SetXY(16,82);	$pdf->Cell(0,3.5, 'Kasubsi HPC:', '0', 0, 'L');//Kasubsi HPC
		$pdf->SetXY(16,87);	$pdf->Cell(0,3.5, 'Penelitian kesesuaian data', '0', 0, 'L');
		
		$pdf->SetXY(16,100);$pdf->Cell(0,3.5, 'Catatan : Identitas Holder : Sesuai / Tidak sesuai *)', '0', 0, 'L');
		$pdf->SetXY(31,106);$pdf->Cell(0,3.5, 'Masa berlaku : Aktif / Masa Berlaku Habis *)', '0', 0, 'L');
		$pdf->SetXY(31,112);$pdf->Cell(0,3.5, 'Pemeriksa yang ditunjuk :', '0', 0, 'L');
		
		$pdf->SetXY(7,121);	$pdf->Cell(0,3.5, '4.', '0', 0, 'L');//no 4
		$pdf->SetXY(16,121);$pdf->Cell(0,3.5, 'Pemeriksa Fisik', '0', 0, 'L');
		
		$pdf->SetXY(16,131);$pdf->Cell(0,3.5, 'Catatan Hasil Pemeriksaan Fisik :', '0', 0, 'L');
		
		$pdf->SetXY(7,157);	$pdf->Cell(0,3.5, '5.', '0', 0, 'L');//no 5
		$pdf->SetXY(16,157);$pdf->Cell(0,3.5, 'Kasubsi HPC:', '0', 0, 'L');
		$pdf->SetXY(16,162);$pdf->Cell(0,3.5, 'Pengisian dan Penandasahan', '0', 0, 'L');
		$pdf->SetXY(16,166);$pdf->Cell(0,3.5, 'Carnet', '0', 0, 'L');
		
		
		$pdf->SetXY(7,172);	$pdf->Cell(0,3.5, '6.', '0', 0, 'L');//no 6
		$pdf->SetXY(16,172);$pdf->Cell(0,3.5, 'Pelaksana Administrasi:', '0', 0, 'L');
		$pdf->SetXY(16,177);$pdf->Cell(0,3.5, 'Penatausahaan', '0', 0, 'L');
		
		$pdf->SetXY(8,186);	$pdf->Cell(0,3.5, '*)Coret salah satu', '0', 0, 'L');
		
		//KANAN
	    $pdf->SetFont('Arial','','9');
		$pdf->SetXY(152, 16); $pdf->Cell(0,3.5, 'No.Agenda:', '0', 0, 'L');
		$pdf->SetXY(152, 21); $pdf->Cell(0,3.5, 'Carnet: '.$jenis.' No.Carnet:', '0', 0, 'L');		
		$pdf->SetXY(152, 26); $pdf->Cell(0,3.5, 'Nama Carnet Holder:', '0', 0, 'L');	
		$pdf->SetXY(152, 31);	$pdf->Cell(0,3.5, 'Rencana Ekspor:Tgl/bln/thn', '0', 0, 'L');
			$pdf->SetXY(225, 31); $pdf->Cell(0,3.5, 'Lokasi:', '0', 0, 'L');
			
		$pdf->SetXY(151,41);	$pdf->Cell(0,3.5, 'No.', '0', 0, 'L');
		$pdf->SetXY(176,41);	$pdf->Cell(0,3.5, 'Kegiatan', '0', 0, 'L'); //kegiatan
		$pdf->SetXY(207,41);	$pdf->Cell(0,3.5, 'Tanggal', '0', 0, 'L'); //tanggal
		$pdf->SetXY(235,39);	$pdf->Cell(0,3.5, 'Jam', '0', 0, 'L'); //jam
		$pdf->SetXY(223,44);	$pdf->Cell(0,3.5, 'Diterima', '0', 0, 'L'); //diterima
		$pdf->SetXY(241,44);	$pdf->Cell(0,3.5, 'Selesai', '0', 0, 'L'); //selesai
		$pdf->SetXY(258,41);$pdf->Cell(0,3.5, 'Paraf', '0', 0, 'L'); //paraf
		$pdf->SetXY(274,41);$pdf->Cell(0,3.5, 'Nama', '0', 0, 'L'); //nama	
		
		$pdf->SetXY(151,49);	$pdf->Cell(0,3.5, '1.', '0', 0, 'L');//no 1
		$pdf->SetXY(160,49);	$pdf->Cell(0,3.5, 'Pelaksana Administrasi:', '0', 0, 'L');//Pelaksana adm
		$pdf->SetXY(160,54);	$pdf->Cell(0,3.5, 'Penerimaan dan penelitian', '0', 0, 'L');//penerimaan dan penelitian
		$pdf->SetXY(160,58);	$pdf->Cell(0,3.5, 'kelengkapan dokumen', '0', 0, 'L');
		
		$pdf->SetXY(151,66);	$pdf->Cell(0,3.5, '2.', '0', 0, 'L');//no 2
		$pdf->SetXY(160,66);	$pdf->Cell(0,3.5, 'Kepala Seksi PKC:', '0', 0, 'L');//Kepala Seksi HPC
		$pdf->SetXY(160,71);	$pdf->Cell(0,3.5, 'Penunjukkan Kasubsi HPC', '0', 0, 'L');//penunjukan
		
		$pdf->SetXY(151,82);	$pdf->Cell(0,3.5, '3.', '0', 0, 'L');//no 3
		$pdf->SetXY(160,82);	$pdf->Cell(0,3.5, 'Kasubsi HPC:', '0', 0, 'L');//Kasubsi HPC
		$pdf->SetXY(160,87);	$pdf->Cell(0,3.5, 'Penelitian kesesuaian data', '0', 0, 'L');
		
		$pdf->SetXY(160,100);$pdf->Cell(0,3.5, 'Catatan : Identitas Holder : Sesuai / Tidak sesuai *)', '0', 0, 'L');
		$pdf->SetXY(175,106);$pdf->Cell(0,3.5, 'Masa berlaku : Aktif / Masa Berlaku Habis *)', '0', 0, 'L');
		$pdf->SetXY(175,112);$pdf->Cell(0,3.5, 'Pemeriksa yang ditunjuk :', '0', 0, 'L');
		
		$pdf->SetXY(151,121);	$pdf->Cell(0,3.5, '4.', '0', 0, 'L');//no 4
		$pdf->SetXY(160,121);$pdf->Cell(0,3.5, 'Pemeriksa Fisik', '0', 0, 'L');
		
		$pdf->SetXY(160,131);$pdf->Cell(0,3.5, 'Catatan Hasil Pemeriksaan Fisik :', '0', 0, 'L');
		
		$pdf->SetXY(151,157);	$pdf->Cell(0,3.5, '5.', '0', 0, 'L');//no 5
		$pdf->SetXY(160,157);$pdf->Cell(0,3.5, 'Kasubsi HPC:', '0', 0, 'L');
		$pdf->SetXY(160,162);$pdf->Cell(0,3.5, 'Pengisian dan Penandasahan', '0', 0, 'L');
		$pdf->SetXY(160,166);$pdf->Cell(0,3.5, 'Carnet', '0', 0, 'L');
		
		
		$pdf->SetXY(151,172);	$pdf->Cell(0,3.5, '6.', '0', 0, 'L');//no 6
		$pdf->SetXY(160,172);$pdf->Cell(0,3.5, 'Pelaksana Administrasi:', '0', 0, 'L');
		$pdf->SetXY(160,177);$pdf->Cell(0,3.5, 'Penatausahaan', '0', 0, 'L');
		
		$pdf->SetXY(152,186);	$pdf->Cell(0,3.5, '*)Coret salah satu', '0', 0, 'L');
		
	    $pdf->Output();
	
	}
}
?>