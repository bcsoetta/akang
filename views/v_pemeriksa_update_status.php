<?php
// print_r($pemeriksa);

$status_desc = 'TIDAK-DIABSEN';
$status = null;
$role = '-';
$lokasi = '-';
$waktu = '-';

if (isset($pemeriksa['role'])) {
	$txt = implode(',', $pemeriksa['role']);
	if (strlen($txt) >= 3)
		$role = $txt;
}

if (isset($pemeriksa['lokasi'])) {
	$txt = implode(',', $pemeriksa['lokasi']);
	if (strlen($txt) >= 3)
		$lokasi = $txt;
}

if (isset($pemeriksa['status_desc']))
	$status_desc = $pemeriksa['status_desc'];

if (isset($pemeriksa['status']))
	$status = $pemeriksa['status'];

if (isset($pemeriksa['stat_time']))
	$waktu = $pemeriksa['stat_time'];

// print_r($pemeriksa);
?>

<div>
	<form method="POST" action="">
		<h2>
			Status
		</h2>
		<p id="status">
			<?php
			echo $status_desc;
			?>
		</p>

		<h2>
			Bertugas sebagai pemeriksa
		</h2>
		<p id="role">
			<?php
			echo $role;
			?>
		</p>
		<h2>
			Lokasi gudang
		</h2>
		<p id="lokasi">
			<?php
			echo $lokasi;
			?>
		</p>

		<h2>
			Waktu Status Terakhir
		</h2>
		<p>
			<?php
			// tampilkan waktu terakhir status
			echo $waktu;
			?>
		</p>
		<hr>
		<div>
			<button data-url="<?php echo base_url('pemeriksa/update/status/available');?>" class="shAnim commonButton redGrad" <?php if($status == 'AVAILABLE' || is_null($status))echo 'disabled';?>>Siap ditugaskan kembali</button>
			<button data-url="<?php echo base_url('pemeriksa/update/status/non-aktif');?>" class="shAnim commonButton greenGrad" <?php if($status == 'NON-AKTIF' || is_null($status))echo 'disabled';?> >Absen Pulang/Non-Aktif</button>
		</div>
	</form>
</div>