<?php
if (!isset($listLokasi)) {
	$listLokasi = array();
}

if (!isset($role)) {
	$role = array();
}
?>
<div>
	<form id="frmCariDokumen" method="POST" action="<?php echo base_url('pemeriksa/getdokumen');?>">
	<h2>
		List Dokumen
	</h2>
	<p>
		<label class="pointable">
			<input type="radio" id="rbPIB" name="doctype" value="PIB" class="pointable baseline" <?php if(!in_array('PIB', $role))echo 'disabled'; ?>/>
			PIB	
		</label>
		
		<label class="pointable">
			<input type="radio" id="rbCNPIBK" name="doctype" value="CN_PIBK" class="pointable baseline" <?php if(!in_array('CN_PIBK', $role))echo 'disabled'; ?>/>	
			CN/PIBK
		</label>
	</p>
	<h2>
		Lokasi
	</h2>
	<p>
		<select multiple name="lokasi[]">
			<?php
			foreach ($listLokasi as $lokasi) {
				
			?>
			
			<option value="<?php echo $lokasi?>"><?php echo $lokasi?></option>
			
			<?php
			}
			?>
		</select>
	</p>
	
	<p>
		<input type="submit" value="Cari" class="shAnim commonButton blueGrad" />	
	</p>

	<hr>

	</form>
	
	<form id="frmListDokumen" method="POST" action="<?php echo base_url('pemeriksa/flag/selesai')?>">
		<table class="table" id="tblDokumen">
			<thead>
				<tr>
					<th>No</th>
					<th>Jenis</th>
					<th>No Dok</th>
					<th>Tgl Dok</th>
					<th>Importir</th>
					<th>Jumlah Item</th>
					<th>Berat (Kg)</th>
					<th>Lokasi</th>
					<th>Aksi</th>
					<th>
						<label class="pointable ifullblock">
							<input id="selAllDok" type="checkbox" class="pointable ifullblock" value="21" />
						</label>
					</th>
				</tr>
			</thead>
			<tbody>
				<!-- <tr>
					<td>1</td>
					<td>CN/PIBK</td>
					<td>SQ99231290323PL</td>
					<td>12/01/2018</td>
					<td>Ms. Trinity</td>
					<td>FEDX</td>
					<td>
						<button class="commonButton shAnim redGrad">Selesai</button>
					</td>
					<td>
						<label class="pointable ifullblock">
							<input type="checkbox" class="pointable ifullblock btnSelesai" name="dokumen[]" value="21" />
						</label>
					</td>
				</tr> -->
			</tbody>
		</table>
		
		<hr>

		<div>
			<button class="commonButton shAnim redGrad" id="btnSelesaikan">Selesaikan</button>
		</div>
	</form>
</div>

<div style="display:none;">
	<table id="protoTblDokumen">
		<tr>
			<td>1</td>
			<td>CN/PIBK</td>
			<td>SQ99231290323PL</td>
			<td>12/01/2018</td>
			<td>Ms. Trinity</td>
			<td>10</td>
			<td>0.5000</td>
			<td>FEDX</td>
			<td>
				<button class="commonButton shAnim redGrad btnSelesai" data-url="<?php echo base_url('pemeriksa/flag/selesai');?>" data-dokumen="">Selesai</button>
			</td>
			<td>
				<label class="pointable ifullblock">
					<input type="checkbox" class="pointable ifullblock" name="dokumen[]" value="" />
				</label>
			</td>
		</tr>
	</table>
</div>

<div id="blocker">
	<div class="dialog">
		<h2 id="blockerText">
		Parsing Data...
		</h2>
		<div id="spinnerBox">
			<div class="spinner">
			</div>
		</div>
	</p>
</div>

<script type="text/javascript">
var msSettings = {
	search: true,
	columns: 4,
	selectAll: true,
	texts: {
		placeholder: 'Pilih gudang',
		search: 'Ketik gudang yang dicari'
	}
};
// munculin blocker
function showBlocker(msg, progressbar, progress) {
	if (msg) {
		$('#blockerText').text(msg);
	}

	$('#blocker').show();
}

// sembunyiin blocker
function hideBlocker() {
	$('#blocker').hide();
}

function updateGroupDokumenCheckBox() {
	var cbCount = parseInt( $('#tblDokumen input[name="dokumen[]"]').length );
	var cbSelCount = parseInt( $('#tblDokumen input[name="dokumen[]"]:checked').length );

	// console.log("Total: " + cbCount + ", Selected: " + cbSelCount);

	// if ratio is 100%, check full
	// if ratio is 0%, uncheck full
	// else, half state
	if (cbCount == cbSelCount && cbCount > 0) {
		$('#selAllDok').prop('checked', true);
		$('#selAllDok').prop('indeterminate', false);
	} else if (cbSelCount == 0) {
		$('#selAllDok').prop('checked', false);
		$('#selAllDok').prop('indeterminate', false);
	} else {
		$('#selAllDok').prop('checked', true);
		$('#selAllDok').prop('indeterminate', true);
	}
}

function clearTabelDokumen() {
	$('#tblDokumen tbody tr').remove();
}

function addRowDokumen(doc_id, doctype, nodok, tgldok, importir, jml_item, berat, lokasi, image) {
	var row = $('#protoTblDokumen tr').clone();

	var rowCount = $('#tblDokumen tbody tr').length;

	var td = $(row).find('td');

	$(td[0]).text(rowCount+1);	// nomor
	$(td[1]).text(doctype);
	$(td[2]).text(nodok);
	$(td[3]).text(tgldok);
	$(td[4]).text(importir);

	$(td[5]).text(jml_item);
	$(td[6]).text(berat);

	$(td[7]).text(lokasi);
	$(td[8]).find('button').attr('data-dokumen', doc_id);
	$(td[9]).find('input').val(doc_id);

	
	if (image)
		var img = $('<img>').attr('src', image).addClass('imagePreview').appendTo(td[2]);
	// console.log(row);

	$('#tblDokumen tbody').append(row);
}

$(function() {
	// sembunyiin tabel dlu
	$('#tblDokumen').hide();

	// multi select
	$('select[name="lokasi[]"]').multiselect(msSettings);

	// pas klik checkbox
	$('#tblDokumen').on('change', 'input[name="dokumen[]"]', function() {
		updateGroupDokumenCheckBox();

		// tambah list dok
		if ($(this).prop('checked')) {
			$(this).closest('tr').addClass('rowselected');
		} else {
			$(this).closest('tr').removeClass('rowselected');
		}
	});

	// pas klik group checkbox
	$('#selAllDok').change(function() {
		// updateGroupDokumenCheckBox();
		$('#tblDokumen input[name="dokumen[]"]').prop('checked', $(this).prop('checked'));
		// alert($(this).prop('checked'));
		$('#tblDokumen input[name="dokumen[]"]').change();
	});

	// when search form submit
	$('#frmCariDokumen').submit(function(e) {
		e.preventDefault();
		e.stopPropagation();	

		// validasi dlu
		var validated = $('input[name="doctype"]:checked').length > 0
						&& $('select[name="lokasi[]"]').val() !== null;

		if (!validated) {
			alert("Parameter pencarian kurang. Cek lagi.");

			return false;
		}

		// lolos validasi, kirim lah
		var url = this.action;
		var fd = new FormData(this);

		var doctype = $('input[name="doctype"]:checked').val();
		var msg = "Mencari dokumen PIB outstanding...";

		if (doctype != 'PIB')
			msg = "Mencari dokumen CN/PIBK outstanding...";

		showBlocker(msg);

		$.ajax({
			url: url,
			data: fd,
			type: 'POST',
			processData: false,
			contentType: false,

			success: function(data, status, jqXHR) {
				

				console.log(data);

				if (data.dokumen.length < 1) {
					$('#tblDokumen').hide();
					alert("Tidak ada dokumen outstanding");
					hideBlocker();
					return false;
				}

				// clear tabel
				clearTabelDokumen();

				// generate tabel
				for (var i=0; i<data.dokumen.length; i++) {
					var dok = data.dokumen[i];

					addRowDokumen(
						dok.dok_id,
						dok.jenis_dok,
						dok.no_dok,
						dok.tgl_formatted,
						dok.importir,
						dok.jml_item,
						dok.berat_kg,
						dok.gudang,
						dok.img_filename
						);
				}

				hideBlocker();
				$('#tblDokumen').show();
			},

			error: function(jqXHR, status, errorObj) {
				alert('Error: ' + jqXHR.statusText);
				hideBlocker();
			}
		});
	});

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

	// pas klik tombol selesai
	$('#tblDokumen').on('click', '.btnSelesai', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var url = $(this).data('url');
		var doc_id = $(this).data('dokumen');

		var completeURL = url + '/' + doc_id;

		// alert(completeURL);
		showBlocker("Flag dokumen selesai...");
		var jqXHR = $.get(completeURL, function(data) {
			// just click teh search button again
			$('#frmCariDokumen').submit();
			// alert(data);
		})
			.fail(function() {
				alert("Error flagging dokumen! : "+jqXHR.statusText);

				console.log(jqXHR);
			})
			.always(function() {
				hideBlocker();
			});
	});

	// massive flag
	$('#frmListDokumen').submit(function(e) {
		e.preventDefault();
		e.stopPropagation();

		var url = this.action;
		var fd = new FormData(this);

		showBlocker("Flag dokumen selesai...");
		$.ajax({
			url: url,
			data: fd,
			type: 'POST',
			processData: false,
			contentType: false,

			success: function(data, status, jqXHR) {
				// alert(data);
				// refresh list
				$('#frmCariDokumen').submit();

				hideBlocker();
			},

			error: function(jqXHR, status, errorObj) {
				alert("Error flagging dokumen! : " + jqXHR.statusText);

				hideBlocker();
			}
		});
	});
});

</script>