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
		<div style="display: inline-block;">
			<select class="styled shAnim" id="status" name="status">
				<option value="ON_PROCESS" selected>OUTSTANDING</option>
				<option value="OVERTIME">OVERTIME</option>
			</select>
		</div>
	</h2>
	<p>
		<label class="pointable">
			<input type="radio" id="rbCARNET" name="doctype" value="CARNET" class="pointable baseline" <?php if(!in_array('CARNET', $role))echo 'disabled'; ?>/>	
			CARNET
		</label>

		<!-- <label class="pointable">
			<input type="radio" id="rbPIB" name="doctype" value="PIB" class="pointable baseline" <?php if(!in_array('PIB', $role))echo 'disabled'; ?>/>
			PIB	
		</label> -->
		
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
					<!-- <th>
						<label class="pointable ifullblock">
							<input id="selAllDok" type="checkbox" class="pointable ifullblock" value="21" />
						</label>
					</th> -->
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
		
		<!-- <hr>

		<div>
			<button class="commonButton shAnim redGrad" id="btnSelesaikan">Selesaikan</button>
		</div> -->

		
		<div id="dlgKeputusan" class="keputusan graybox" style="display:none;">
			<button class="commonButton shAnim greenGrad btnSelesai" data-url="<?php echo base_url('pemeriksa/flag/selesai');?>" >Sesuai</button>
			<a href="javascript:void(0)" class="commonButton shAnim redGrad btnPopupCatatan"  >Tidak Sesuai</a>

			<a href="javascript:void(0)" class="commonButton yellowGrad" id="closeDlgKeputusan" onclick="closeDlgKeputusan()">Tutup</a>
		</div>


		<div id="dlgCatatanPemeriksa" class="floatForm graybox" style="display:none;">
			<h2>Catatan</h2>
			<p>
				<textarea id="txtCatatan" style="width:100%;" rows="8"></textarea>	
			</p>
			<div class="bottombox">
				<a href="javascript:void(0)" class="commonButton shAnim blueGrad" id="btnSimpanCatatan">Simpan</a>
				<a href="javascript:void(0)" class="commonButton shAnim redGrad" onclick="closeDlgCatatan()">Batal</a>		

				<button class="btnSelesai" style="display: none;" id="btnDummy" data-url="<?php echo base_url('pemeriksa/flag/tidaksesuai');?>"></button>
			</div>
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
				<button class="commonButton shAnim blueGrad btnSelesaikan"  data-dokumen="">Selesaikan</button>
				<button class="commonButton shAnim redGrad btnSelesai" data-url="<?php echo base_url('pemeriksa/flag/overtime');?>" data-dokumen="">Overtime!</button>
			</td>
			<!-- <td>
				<label class="pointable ifullblock">
					<input type="checkbox" class="pointable ifullblock" name="dokumen[]" value="" />
				</label>
			</td> -->
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

function closeDlgKeputusan() {
	$('#dlgKeputusan').hide();
	$('.rowselected').removeClass('rowselected');
}

function closeDlgCatatan() {
	$('#dlgCatatanPemeriksa').hide();
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
		var status = $('#status').val();

		var msg = "Mencari dokumen "+doctype+" "+status+"...";

		// if (doctype != 'CARNET')
		// 	msg = "Mencari dokumen CN/PIBK outstanding...";

		showBlocker(msg);

		$.ajax({
			url: url,
			data: fd,
			type: 'POST',
			processData: false,
			contentType: false,

			success: function(data, status, jqXHR) {
				

				// console.log(data);

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

	// munculin popup dialog keputusan
	$('.btnPopupCatatan').click(function(e) {
		e.preventDefault();
		e.stopPropagation();

		$('#dlgCatatanPemeriksa').show();
	});

	// tombol utk simpan catatan
	$('#btnSimpanCatatan').click(function (e) {
		e.preventDefault();
		e.stopPropagation();

		// ok, isi data dummy dulu.
		var catatan = $('#txtCatatan').val();

		// masukin ke btn dummy
		$('#btnDummy').data('catatan', decodeURIComponent(catatan)).click();

		// close semua
		closeDlgCatatan();
		closeDlgKeputusan();
	});

	// pas klik tombol selesaikan
	$('#tblDokumen').on('click', '.btnSelesaikan', function(e) {
		e.preventDefault();
		e.stopPropagation();

		// console.log($(this).position());
		var pos = $(this).position();
		var width = $('#dlgKeputusan').width();
		var dokid = $(this).data('dokumen');
		// console.log(pos);
		// console.log(dokid);

		$(this).closest('tr').addClass("rowselected");

		// munculkan popup
		$('#dlgKeputusan').show().offset({top: pos.top, left: pos.left-width});
		// set popup data
		$('#dlgKeputusan .btnSelesai').data('dokumen', dokid);
		$('#btnDummy').data('dokumen', dokid);

		// console.log($('#dlgKeputusan').position());
	}); 

	// pas klik tombol selesai
	$('#frmListDokumen').on('click', '.btnSelesai', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var url = $(this).data('url');
		var doc_id = $(this).data('dokumen');
		var catatan = $(this).data('catatan');

		var completeURL = url + '/' + doc_id;

		// if there's catatan, append it
		if (typeof catatan != 'undefined')
			completeURL += '/' + catatan;

		console.log(completeURL);

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

				// hide popup
				// $('#dlgKeputusan').hide();
				closeDlgKeputusan();
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