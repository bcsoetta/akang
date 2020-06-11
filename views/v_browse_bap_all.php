<div id="toolbox">
	<form action="<?php echo base_url('pemeriksa/browsebap');?>" id="parambox" method="POST">
		<p><strong>BROWSE BAP</strong><p>
		<hr>
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
	<!-- <searchparam>
		<?php
		print_r($searchParam);
		?>
	</searchparam>

	<searchresult>
		<?php
		print_r($searchResult);
		?>
	</searchresult> -->
	<?php
	if (!isset($searchResult)) {
	?>
	<!-- <p>Data BAP untuk parameter di atas tidak ditemukan</p> -->
	<table class="table">
		<thead>
			<tr>
				<th>Data not found</th>
			</tr>
		</thead>
		<tr>
			<td>
				Maaf data tidak ditemukan
			</td>
		</tr>
	</table>
	<?php
	} else {
		$no = 1;
	?>

	<!-- <pre><?php print_r($searchResult); ?></pre> -->

	<table id="listBap" class="table">
		<thead>
			<tr>
				<th>No</th>
				<th>Pemeriksa</th>
				<th>NIP</th>
				<th>Nomor BAP</th>
				<th>Tgl BAP</th>
				<th>PJT</th>
				<th>Lokasi</th>
				<th>Total HAWB</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (is_array($searchResult['data']))
			foreach($searchResult['data'] as $data) {
			?>

			<tr>
				<td><?php echo $no++;?></td>
				<td><?php echo $data['fullname'];?></td>
				<td><?php echo $data['nip'];?></td>
				<td><?php echo $data['nomor_lengkap'];?></td>
				<td><?php echo $data['tanggal_formatted'];?></td>
				<td><?php echo $data['pjt'];?></td>
				<td><?php echo $data['gudang'];?></td>
				<td><?php echo $data['total_hawb'];?></td>
				<td>
					<p>
						<a href="<?php echo base_url('app/bappdf/' . $data['id']);?>" target="_blank" class="commonButton redGrad shAnim">Cetak</a>
					</p>
				</td>
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

	// auto sum
	$.each($('.subtotal'), function(k, v) {
		var sum = 0;
		$.each( $('.summable'+(k+1)), function(kk, vv){
			sum += parseInt($(vv).text());
		} );

		$(v).text(sum);
	});

	$.each($('.subtotalsesuai'), function(k, v) {
		var sum = 0;
		$.each( $('.FINISHED'+(k+1)), function(kk, vv){
			sum += parseInt($(vv).text());
		} );

		$(v).text(sum);
	});	

	$.each($('.subtotaltidaksesuai'), function(k, v) {
		var sum = 0;
		$.each( $('.INCONSISTENT'+(k+1)), function(kk, vv){
			sum += parseInt($(vv).text());
		} );

		$(v).text(sum);
	});	
});
</script>