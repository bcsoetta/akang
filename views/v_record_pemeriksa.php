<h2>
	Record Pemeriksaan <?php echo $fullname . '@' . $gudang . '@' . $tglPeriksa . ' (' . $doctype.')' ?>
</h2>

<div>
	<table class="table">
		<thead>
			<tr>
				<th>No</th>
				<th>No Dokumen</th>
				<th>Tanggal</th>
				<th>Importir</th>
				<th>Jml Item</th>
				<th>Berat (kg)</th>
				<th>Putusan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$no = 1;

			$putusan = array(
				'FINISHED'		=> 'SESUAI',
				'INCONSISTENT'	=> 'TIDAK SESUAI',
				'OVERTIME'		=> 'OVERTIME'
			);

			foreach ($data as $row) {
				
			?>

			<tr>
				<td><?php echo $no++; ?></td>
				<td><?php echo $row['no_dok'];?></td>
				<td><?php echo $row['tgl_dok'];?></td>
				<td><?php echo $row['importir'];?></td>
				<td><?php echo $row['jml_item'];?></td>
				<td><?php echo $row['berat_kg'];?></td>
				<td><?php echo $putusan[$row['status'] ];?></td>
			</tr>

			<?php
			}
			?>
		</tbody>
	</table>
</div>