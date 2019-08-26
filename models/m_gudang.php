<?php
/*
Model : Gudang
berisi method untuk memanipulasi data gudang
*/

class gudang extends Base_Model {

    public function __construct() {
        parent::__construct();

        $this->load_db();
    }

    public function getListGudang() {
        // ambil gudang selain 'BCSH'

        $q_get_valid_gudang = "
            SELECT * FROM grup_gudang WHERE gudang <> 'BCSH' ORDER BY grup
        ";

        $listGudang = [];

        try {
            //code...
            $stmtGetValidGudang = $this->db->prepare($q_get_valid_gudang);
            $stmtGetValidGudang->execute();

            $rows = $stmtGetValidGudang->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $listGudang[] = $row;
            }
        } catch (PDOException $e) {
            $this->setLastError($e->getMessage());
            return false;
        }

        return $listGudang;
    }

    public function delete($kdGudang) {
        if (!isset($kdGudang)) {
            $this->setLastError("Kode gudang kosong.");
            return false;
        }

        // now let's try it
        $q_delete_gudang = "
            DELETE FROM grup_gudang WHERE gudang = :kd_gudang
        ";
        

        try {
            // coba eksekusi
            $stmtDelete = $this->db->prepare($q_delete_gudang);
            $stmtDelete->execute([
                'kd_gudang' => trim($kdGudang)
            ]);
        } catch (PDOException $e) {
            $this->setLastError($e->getMessage());
            return false;
        }

        if ($stmtDelete->rowCount() < 1) 
            $this->setLastError("Kode gudang tidak ditemukan.");

        return $stmtDelete->rowCount() > 0;
    }

    public function add($kdGudang, $grupGudang) {
        // both must be set
        $kdGudang = trim($kdGudang);
        $grupGudang = trim($grupGudang);

        if (strlen($kdGudang) < 1 || strlen($grupGudang) < 1) {
            $this->setLastError("Kode Gudang atau Grup Gudang kosong.");
            return false;
        }
        
        // try to insert it now
        $q_insert = "INSERT INTO grup_gudang(gudang, grup) VALUES (:kdGudang, :grpGudang)";

        try {
            $stmtInsert = $this->db->prepare($q_insert);

            $stmtInsert->execute([
                'kdGudang' => $kdGudang,
                'grpGudang' => $grupGudang
            ]);

        } catch (PDOException $e) {
            $this->setLastError($e->getMessage());
            return false;
        }

        // big if true
        return true;
    }
}
?>