<?php
class C_gudang extends Base_Controller {
    public function __construct() {
        parent::__construct();
        // load model used
        $this->load_model('user');
        $this->load_model('gudang');
        $this->load_model('app');
    }

    // uri utk menghapus gudang
    // BASE_URI/gudang/delete/{kode_gudang}
    public function delete($kodeGudang) {
        // kode gudang harus diset
        // dan user harus superuser
        // dan harus sudah login
        if (!isset($kodeGudang) || !$this->user->hasRole(R_SUPERUSER) || !$this->user->isLoggedIn())
            return forbid();

        // aman, eksekusi
        if ($this->gudang->delete($kodeGudang)) {
            $msg = "Gudang '" . $kodeGudang ."' berhasil dihapus";
        } else {
            $msg = "Gagal menghapus gudang '" . $kodeGudang . "'. alasan: " . $this->gudang->getLastError();
        }

        // gunakan halaman redirection
		$data = array(
			'pagetitle' => 'Menghapus gudang...',
			'message' => $msg,
			'seconds' => 3,
			'targetName' => 'Halaman sebelumnya',
			'target' => base_url('app/manage/gudang')
			);

		$this->load_view('message_redirect', $data, false);
    }
}
?>