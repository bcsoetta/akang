<div id="toolbox">
	<form action="<?php echo base_url('pemeriksa/performa');?>" id="parambox" method="POST">
		<p>
			<span class="field">Dari</span>
			<input id="datestart" class="datepicker shAnim shInput" name="datestart" type="text" value="<?php echo $searchParam['datestart'];?>"/>
			<span>s/d&nbsp;</span>
			<input id="dateend" class="datepicker shAnim shInput" name="dateend"  type="text" value="<?php echo $searchParam['dateend'];?>" />
		</p>
		<p>
			<span class="field">Pemeriksa</span>
			<select id="selectedPemeriksa" name="selectedPemeriksa[]" multiple>

				<?php
				foreach ($searchParam['listPemeriksa'] as $id => $nama) {
					
				?>
				
				<option value="<?php echo $id;?>" <?php if (in_array($id, $searchParam['selectedPemeriksa'])) echo 'selected';?> > <?php echo $nama;?> </option>
				
				<?php

				}
				?>
			</select>
		</p>
		<!-- <p>
			<label>
				<input class="cbDoctype" type="checkbox" name="doctype[]" value="PIB" <?php if (in_array('PIB', $searchParam['doctype'])) echo 'checked';?> >
				PIB
			</label>
			<label>
				<input class="cbDoctype" type="checkbox" name="doctype[]" value="CN_PIBK" <?php if (in_array('CN_PIBK', $searchParam['doctype'])) echo 'checked';?> >
				CN/PIBK
			</label>
		</p> -->
		<p>
			<input class="commonButton blueGrad shAnim submit" name="submit" value="Cari" type="submit" />
		</p>
	</form>
</div>

<div id="searchResult">
	<!-- <p>
		<?php
		print_r($searchParam);
		?>
	</p>
	<p>
		<?php
		print_r($searchResult);
		?>
	</p> -->
	<?php
	if ($searchResult && count($searchResult)) {

		$skipRow = array();
		$skipped = array();

		// process each row data
		foreach ($searchResult as $row) {
			if (isset($skipRow[$row['id']]))
				$skipRow[$row['id']]++;
			else
				$skipRow[$row['id']] = 1;

			$skipped[$row['id']] = 0;
		}
	?>

	<hr>

	<table id="tblResult" class="table">
		<thead>
			<tr>
				<th>No</th>
				<th>Nama Pemeriksa</th>
				<th>Tanggal Periksa</th>
				<th>Gudang</th>
				<th>Total Periksa</th>
				<th>Jenis Dok</th>
			</tr>
		</thead>
		<tbody>

			<?php
			$nomor = 1;
			foreach ($searchResult as $row) {
				$spawnRow = false;
				if ($skipRow[$row['id']] > 1 && $skipped[$row['id']]++ == 0 || $skipRow[$row['id']] == 1)
					$spawnRow = true;
			?>

			<tr>

				<?php
				// cuma utk kolom yg bsa rowspan
				if ($spawnRow) {
					if ($nomor % 2)
						$className = "spannerOdd";
					else
						$className = "spannerEven";
				?>
				
				<td class="<?php echo $className; ?>" rowspan="<?php echo $skipRow[$row['id']] ?>">

					<?php
					echo ($nomor++);
					?>
				</td>
				<td class="<?php echo $className; ?>" rowspan="<?php echo $skipRow[$row['id']] ?>">
					<?php
					echo $row['fullname'];
					?>
				</td>
				
				<?php
				}
				?>

				<td><?php echo $row['tgl_periksa'];?></td>
				<td><?php echo $row['gudang'];?></td>
				<td>
					<?php
					if ($row['gudang'] != '-') {
					?>
					<a target="_blank" href="<?php if($row['gudang'] != '-') echo base_url("pemeriksa/record/$row[id]/$row[tgl_periksa_raw]/$row[gudang]/$row[jenis_dok]/$row[fullname]"); else echo "javascript:void(0);"?>"> <?php echo $row['total_periksa'];?> </a>
					<?php
					} else {
						echo $row['total_periksa'];
					}
					?>

				</td>
				<td><?php echo $row['jenis_dok'];?></td>
			</tr>

			<?php
			}
			?>
			
			
		</tbody>
	</table>	

	<?php
	}
	?>
</div>


<script type="text/javascript">
$(function() {
	// enable multiselect
	$('select#selectedPemeriksa').multiselect({
		columns : 4,
		search : true,
		selectAll : true,
		texts : {
			placeholder: 'Pilih pemeriksa',
			search : 'Ketik nama yang dicari'
		}
	});


	// cek validasi
	$('#parambox').submit(function(e) {
		var validated = true;
		var errorMessage = 'Error:\n';

		

		if ($('#datestart').val().length < 10) {
			validated = false;
			errorMessage += "-Tanggal Awal tidak valid.\n";

			// alert($('#datestart').val().length);
		}

		if ($('#dateend').val().length < 10) {
			validated = false;
			errorMessage += "-Tanggal Akhir tidak valid.\n";

			// alert($('#dateend').val().length);
		}

		if (!$('#selectedPemeriksa').val()) {
			validated = false;
			errorMessage += "-Pemeriksa tidak ada yg dipilih.\n";
		}

		// if ($('.cbDoctype:checked').length < 1) {
		// 	validated = false;
		// 	errorMessage += "-Jenis dokumen kosong.";
		// }

		if (!validated) {
			e.preventDefault();
			e.stopPropagation();

			alert(errorMessage);
		}

		return true;
	});
});
</script>