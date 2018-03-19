<div>
	<h2>
		Batch #<?php echo $batch_id;?>
	</h2>
	<p>
		Lokasi Barang : <span><?php echo $gudang;?></span>
	</p>
	<p>
		Diupload oleh : <span><?php echo $uploader;?></span>
	</p>
	<p>
		Waktu Upload : <span><?php echo $upload_time;?></span>
	</p>
	<hr>
	<h2>List <?php echo $doctype=='CN_PIBK'?'CN/PIBK':$doctype;?></h2>

	<table id="tblListPIB" class="table droppable">
		<thead>
			<tr>
				<th>No.</th>
				<th>Nomor <?php echo $doctype=='CN_PIBK'?'CN/PIBK':$doctype; ?></th>
				<th>Tanggal</th>
				<th><?php echo $doctype=='CN_PIBK'?'Consignee':'Importir';?></th>

				<th>Jumlah Item</th>
				<th>Berat (kg)</th>

				<?php
				if (/*$doctype == 'PIB'*/0) {
				?>

				<th>Foto Barang</th>

				<?php
				}
				?>

				<th>Status</th>
				<th>Waktu</th>
				<!-- <th>Action</th> -->
			</tr>
		</thead>
		<tbody>
			<!-- HERE GOES THE LIST -->
			<?php
			if (isset($data)) {
				$no = 1;
				foreach ($data as $row) {
				
			?>

				<tr>
					<td class="rowNum">
						<?php echo $no++. '.'?>
					</td>
					<td>
						<span >
							<?php echo $row['no_dok'];?>
						</span>
					</td>
					<td>
						<?php echo $row['tgl_dok_formatted'];?>
					</td>
					<td>
						<span class="si">
							<?php echo $row['importir'];?>
						</span>
					</td>

					<td>
						<?php echo $row['jml_item']; ?>
					</td>

					<td>
						<?php echo $row['berat_kg']; ?>
					</td>

				<?php
				if (/*$doctype == 'PIB'*/0) {
				?>

					<td>
						<p class="filename">
							<?php echo $row['filename'];?>
						</p>
						<img src="<?php echo base_url($row['real_filename']);?>" class="imagePreview"/>
					</td>

				<?php
				}
				?>

				<td>
					<?php echo $row['status'];?>
				</td>
				<td>
					<?php echo $row['time_formatted'];?>
				</td>
					
				</tr>

			<?php
				}
			}
			?>
		</tbody>
	</table>
</div> 

<script type="text/javascript">
	$(function() {
		// buat image preview, biar bsa liat image full
		$('body').on('click', '.imagePreview', function() {
			var link = $(this).attr('src');

			// console.log(link);

			if (link) {
				var iframe = '<img src="' + link + '" style="max-width: 85%" />';
				
				var w = window.open('#', '_blank');
				// window.open(link, '_blank');
				w.document.open();
				w.document.write(iframe);
				w.document.close();
				w.location.href = '#';
			} else {
				console.log("Error previewing image");
			}
		});
	});
</script>