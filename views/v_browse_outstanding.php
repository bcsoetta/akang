<?php
if (!isset($listGudang))
	$listGudang = array();

if (!isset($isAuthorized))
	$isAuthorized = false;
?>
<form id="frmBrowseOutstanding" action="<?php echo base_url('app/query/outstanding');?>">
	<p>
	Browse Outstanding Per Gudang
	<select id="gudang" name="gudang[]" multiple>

		<?php
		foreach ($listGudang as $gudang) {
			
		?>
		
		<option value="<?php echo $gudang;?>"> <?php echo $gudang;?> </option>
		
		<?php

		}
		?>
	</select>
	</p>
	<p>
		<input type="submit" value="Cari" class="shAnim commonButton blueGrad" />
		<!-- <input type="button" id="btnTest" value="Test" class="shAnim commonButton greenGrad" /> -->
	</p>
</form>

<hr>

<div class="hiddencontent">
	<p>
		<label class="pointable">
			<input type="checkbox" id="cbShowDetailPemeriksa" class="pointable" data-url="<?php echo base_url('pemeriksa/busy');?>"/>
			Tampilkan detil pemeriksa
		</label>
	</p>

	<table class="table" id="tblOutstanding">
		<thead>
			<tr>
				<th rowspan="2" style="vertical-align: middle;" class="blackish">No.</th>
				<th rowspan="2" style="vertical-align: middle;" class="blackish">Gudang</th>
				<th colspan="3">CARNET</th>
				
				<th colspan="3" class="bluish">CN/PIBK</th>

				<?php
				if ($isAuthorized) {
				?>

				<th rowspan="2" style="vertical-align: middle; position: relative;" class="blackish">
					<label class="ifullblock pointable">
						<input type="checkbox" id="selGudang" />
					</label>
				</th>

				<?php
				}
				?>

			</tr>
			<tr>
				<th>Outstanding</th>
				<th>Oldest Doc</th>
				<th>Pemeriksa</th>
				<th class="bluish">Outstanding</th>
				<th class="bluish">Oldest Doc</th>
				<th class="bluish">Pemeriksa</th>
			</tr>
		</thead>
		<tbody>
			<!-- <tr>
				<td>1.</td>
				<td>GDHL</td>
				
				<td>20</td>
				<td>01/01/2018 (10 hari)</td>
				<td>0</td>
				
				<td>120</td>
				<td>31/12/2017 (11 hari)</td>
				<td>2</td>

				<td>
					<label class="ifullblock pointable">
						<input type="checkbox" name="gudang_sel[]" value="GIMP"/>
					</label>
				</td>
			</tr> -->
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2" class="blackish summary">TOTAL</td>
				<td class="blackish summary"><strong id="totalOutPIB">0</strong></td>
				<td class="blackish summary"></td>
				<td class="blackish summary"><strong id="totalPemPIB">0</strong></td>

				<td class="blackish summary"><strong id="totalOutCNPIBK">0</strong></td>
				<td class="blackish summary"></td>
				<td class="blackish summary"><strong id="totalPemCNPIBK">0</strong></td>

				<?php
				if ($isAuthorized) {
				?>
				
				<td class="blackish summary"></td>

				<?php
				}
				?>

			</tr>
		</tfoot>
	</table>

<?php
if ($isAuthorized) {
?>

	<hr>

	<form id="frmPenugasanPemeriksa" action="<?php echo base_url('pemeriksa/penugasan');?>">
		<div class="bottombox">
			<button class="shAnim commonButton blueGrad btnKirimPemeriksa" data-type="CARNET" data-url="<?php echo base_url('pemeriksa/available/CARNET');?>">Kirim Pemeriksa CARNET</button>
			<button class="shAnim commonButton redGrad btnKirimPemeriksa" data-type="CN/PIBK" data-url="<?php echo base_url('pemeriksa/available/CN_PIBK');?>">Kirim Pemeriksa CN/PIBK</button>
		</div>

		<div class="pembox">

		</div>
	</form>

<?php
}
?>

</div>
<!-- Form isi list PEMERIKSA -->

<?php 
// klo bukan yg pny otoritas pabean, jgn kasi form ini bahaya
if ($isAuthorized) {
?>

<div class="floatForm" id="dlgPemeriksa">
	<form action="<?php echo base_url('pemeriksa/penugasan');?>" method="POST" id="frmKirimPemeriksa">
		<p>
			<span class="fc1">Kirim Pemeriksa <span id="spPemeriksa">CARNET</span> Ke :</span>

			<span class="fc2"><input type="text" class="shAnim fullsize" readonly name="lokasi" id="lokasiPenugasan"/></span>
		</p>
		<hr>
		<p>
			Pemeriksa Siap Bertugas :
		</p>
		<table id="tblPemeriksaSiap" class="table">
			<thead>
				<tr>
					<th>No</th>
					<th>Nama</th>
					<th>
						<label >
							<input class="ifullblock pointable" type="checkbox" id="selPemeriksa" />
						</label>
					</th>
				</tr>
			</thead>
			<tbody>
				
			</tbody>
		</table>

		<p style="text-align: center; display:none;" id="pNullMsg">
			Tidak ada pemeriksa yang siap ditugaskan.
		</p>
		<hr>
		<p>
			<button type="submit" class="commonButton redGrad shAnim" id="btnKirimPemeriksa">Kirim</button>
			<button type="button" class="commonButton blueGrad shAnim btnFloatClose" id="btnCloseForm">Tutup</button>
		</p>
	</form>
</div>

<?php 
}
?>

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
	var globalData = {};
	// table
	function clearTable() {
		$('#tblOutstanding tbody tr').remove();
	}

	function hideTable() {
		$('.hiddencontent').hide();
	}

	function showTable() {
		$('.hiddencontent').show();
	}

	function addRow(gudang, outPIB, outCNPIBK, lastPIBDate, lastCNPIBKDate, pemAktifPIB, pemAktifCNPIBK) {
		// let's try to validate it
		outPIB = outPIB || '-';
		outCNPIBK = outCNPIBK || '-';
		lastPIBDate = lastPIBDate || '-';
		lastCNPIBKDate = lastCNPIBKDate || '-';

		var rowCount = $('#tblOutstanding tbody tr').length;

		if (parseInt(pemAktifPIB))
			pemAktifPIB = '<strong class="redfont">' + pemAktifPIB + '</strong>';

		if (parseInt(pemAktifCNPIBK))
			pemAktifCNPIBK = '<strong class="redfont">' + pemAktifCNPIBK + '</strong>';

		// apakah user punya otoritas buat macem2?
		var checkBoxEnabled = $('#selGudang').length > 0;

		var checkBox = '';

		// kalo iya, mnculin check box
		if (checkBoxEnabled) 
			checkBox = '<td><label class="ifullblock pointable"><input type="checkbox" name="gudang_sel[]" value="'+gudang+'"/></label></td>';

		var row = '<tr>'
				+ '<td>' + (rowCount+1) + '</td>'
				+ '<td>' + gudang + '</td>'
				+ '<td><strong>' + outPIB + '</strong></td>'
				+ '<td>' + lastPIBDate + '</td>'
				+ '<td><span>' + pemAktifPIB + '</span><ul class="pemPIB '+gudang+'"></ul></td>'
				+ '<td><strong>' + outCNPIBK + '</strong></td>'
				+ '<td>' + lastCNPIBKDate + '</td>'
				+ '<td><span>' + pemAktifCNPIBK + '</span><ul class="pemCNPIBK '+gudang+'"></ul></td>'
				+ checkBox
				+'</tr>';

		$('#tblOutstanding tbody').append(row);
	}

	function setTotal(outPIB, pemPIB, outCNPIBK, pemCNPIBK) {
		$('#totalOutPIB').text(outPIB);
		$('#totalOutCNPIBK').text(outCNPIBK);

		$('#totalPemPIB').text(pemPIB);
		$('#totalPemCNPIBK').text(pemCNPIBK);
	}

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

	function updateGroupCheckBox() {
		var cbCount = parseInt( $('input[name="gudang_sel[]"]').length );
		var cbSelCount = parseInt( $('input[name="gudang_sel[]"]:checked').length );

		// console.log("Total: " + cbCount + ", Selected: " + cbSelCount);

		// if ratio is 100%, check full
		// if ratio is 0%, uncheck full
		// else, half state
		if (cbCount == cbSelCount && cbCount > 0) {
			$('#selGudang').prop('checked', true);
			$('#selGudang').prop('indeterminate', false);
		} else if (cbSelCount == 0) {
			$('#selGudang').prop('checked', false);
			$('#selGudang').prop('indeterminate', false);
		} else {
			$('#selGudang').prop('checked', true);
			$('#selGudang').prop('indeterminate', true);
		}
	}


	function clearTabelPemeriksa() {
		$('#tblPemeriksaSiap tbody tr').remove();
	}

	function addRowPemeriksa(id, fullname) {
		var rowCount = $('#tblPemeriksaSiap tbody tr').length;

		var row = '<tr>'
					+ '<td>' + (rowCount+1) + '</td>'
					+ '<td>' + fullname + '</td>'
					+ '<td><input type="checkbox" class="ifullblock pointable" name="pemeriksa[]" value="'+id+'"/></td>'
				+ '</tr>';

		$('#tblPemeriksaSiap tbody').append(row);
	}

	function clearCheckBoxes() {
		$('input:checked').prop('checked', false);
	}

	function updateGroupPemeriksaCheckBox() {
		var cbCount = parseInt( $('#tblPemeriksaSiap input[name="pemeriksa[]"]').length );
		var cbSelCount = parseInt( $('#tblPemeriksaSiap input[name="pemeriksa[]"]:checked').length );

		// console.log("Total: " + cbCount + ", Selected: " + cbSelCount);

		// if ratio is 100%, check full
		// if ratio is 0%, uncheck full
		// else, half state
		if (cbCount == cbSelCount && cbCount > 0) {
			$('#selPemeriksa').prop('checked', true);
			$('#selPemeriksa').prop('indeterminate', false);
		} else if (cbSelCount == 0) {
			$('#selPemeriksa').prop('checked', false);
			$('#selPemeriksa').prop('indeterminate', false);
		} else {
			$('#selPemeriksa').prop('checked', true);
			$('#selPemeriksa').prop('indeterminate', true);
		}
	}

	function clearDetailPemeriksa() {
		$('.pemPIB *').remove();
		$('.pemCNPIBK *').remove();
	}

	function updateDetailPemeriksa(data) {
		// data.pemeriksa, detail pemeriksa
		// data.gudang, detail gudang
		for (gudang in data.gudang) {
			// yg pib
			// console.log('PIB');

			// overridden for carnet
			if (typeof data.gudang[gudang].CARNET != 'undefined') {
				for (var i=0; i<data.gudang[gudang].CARNET.length; i++) {
					var idPemeriksa = data.gudang[gudang].CARNET[i];

					// console.log(gudang + ' -> ' +idPemeriksa);

					var li = '<li>' + data.pemeriksa[idPemeriksa].fullname + ' @ ' + data.pemeriksa[idPemeriksa].stat_time + '</li>';

					var list = $('.pemPIB.'+gudang);
					// console.log(list);

					$('.pemPIB.'+gudang).append(li);
				}
			}

			

			// yg cn/pibk
			// console.log('CNPIBK');
			if (typeof data.gudang[gudang].CN_PIBK != 'undefined') {
				for (var i=0; i<data.gudang[gudang].CN_PIBK.length; i++) {
					var idPemeriksa = data.gudang[gudang].CN_PIBK[i];

					// console.log(gudang + ' -> ' +idPemeriksa);

					var li = '<li>' + data.pemeriksa[idPemeriksa].fullname + ' @ ' + data.pemeriksa[idPemeriksa].stat_time + '</li>';

					var list = $('.pemCNPIBK.'+gudang);
					// console.log(list);

					$('.pemCNPIBK.'+gudang).append(li);
				}
			}
			
			
		}
	}

$(function() {
	// addRowPemeriksa(2, 'tai');
	// setting behavior dr multiselect
	$('select#gudang').multiselect({
		columns : 4,
		search : true,
		selectAll : true,
		texts : {
			placeholder: 'Pilih gudang',
			search : 'Ketik gudang yang dicari'
		}
	});

	// pas cari gudang...
	$('#frmBrowseOutstanding').submit(function (e){
		e.preventDefault();
		e.stopPropagation();

		// clearCheckBoxes();

		// check data
		var selected = $('select#gudang').val();

		if (!selected) {
			alert("Pilih gudang plis");
			return false;
		}

		// build data, and send
		var fd = new FormData(this);

		// send it
		var frm = this;

		// console.log(url);

		// let's send
		showBlocker('Querying outstanding data...');

		$.ajax({
			url: frm.action,
			type: 'POST',
			data: fd,
			processData: false,
			cache: false,
			contentType: false,

			success: function(data, status, jqXHR) {
				// console.log(data);

				globalData = data;
				// ok

				// iterate over all data
				clearTable();
				showTable();

				var totalOutPIB = 0;
				var totalOutCNPIBK = 0;

				var totalPemPIB = 0;
				var totalPemCNPIBK = 0;

				for (gudang in data) {
					// console.log(gudang);
					// console.log(data[gudang]);
					// console.log(data[gudang]['CN_PIBK']);

					var outPIB = '-';
					var outCNPIBK = '-';
					
					var oldestPIB = '-';
					var oldestCNPIBK = '-';
					
					var pemPIB = 0;
					var pemCNPIBK = 0;

					if (typeof data[gudang]['CARNET'] !== 'undefined') {
						

						if (typeof data[gudang]['CARNET']['ON_PROCESS'] !== 'undefined') {
							outPIB 	= data[gudang]['CARNET']['ON_PROCESS']['total'];
							oldestPIB 	= data[gudang]['CARNET']['ON_PROCESS']['oldest_formatted'] 
										+ ' (' + data[gudang]['CARNET']['ON_PROCESS']['oldest_age'] +' hari)' ;

							totalOutPIB += outPIB;
						}
						pemPIB 	= data[gudang]['CARNET']['pemeriksa_aktif'];

						// save total
						
						totalPemPIB += pemPIB;
					}

					if (typeof data[gudang]['CN_PIBK'] !== 'undefined') {

						if (typeof data[gudang]['CN_PIBK']['ON_PROCESS'] !== 'undefined') {
							outCNPIBK 	= data[gudang]['CN_PIBK']['ON_PROCESS']['total'];
							oldestCNPIBK	= data[gudang]['CN_PIBK']['ON_PROCESS']['oldest_formatted']
											+ ' (' + data[gudang]['CN_PIBK']['ON_PROCESS']['oldest_age'] + ' hari)';	
											
							totalOutCNPIBK += outCNPIBK;
						}
						
						pemCNPIBK 	= data[gudang]['CN_PIBK']['pemeriksa_aktif'];

						// save total
						
						totalPemCNPIBK += pemCNPIBK;
					}
					// var outPIB = (typeof data[gudang]['PIB'] === 'undefined' ) ? '-' : data[gudang]['PIB']['ON_PROCESS']['total'];
					// var outCNPIBK = data[gudang]['CN_PIBK']['ON_PROCESS']['total'];
					
					// var oldestPIB = data[gudang]['PIB']['ON_PROCESS']['oldest_formatted'];
					// var oldestCNPIBK = data[gudang]['CN_PIBK']['ON_PROCESS']['oldest_formatted'];
					// addRow(gudang, data[gudang]['PIB']['ON_PROCESS']['total'], data[gudang]['CN_PIBK']['ON_PROCESS']['total']);
					addRow(gudang, outPIB, outCNPIBK, oldestPIB, oldestCNPIBK, pemPIB, pemCNPIBK);
				}

				// set totale?
				setTotal(totalOutPIB, totalPemPIB, totalOutCNPIBK, totalPemCNPIBK);

				hideBlocker();

				$('#cbShowDetailPemeriksa').change();
			},

			error: function(jqXHR, status, errorObj) {
				
				alert("Error("+jqXHR.status+"):\n"+jqXHR.statusText);
				hideBlocker();
			}
		});

	});

	
	// behavior when checkbox is selected
	$('#tblOutstanding').on('change', 'input[name="gudang_sel[]"]', function() {
		// console.log(this);
		updateGroupCheckBox();

		// grab status
		if ($(this).prop('checked')) {
			$(this).closest('tr').addClass('rowselected');
		} else {
			$(this).closest('tr').removeClass('rowselected');
		}
	});

	// behavior for checkbox
	$('#selGudang').change(function() {
		if ($(this).prop('checked')) {
			// full check
			// console.log('Full check');
			$('input[name="gudang_sel[]"]').prop('checked', true);
		} else {
			// full uncheck
			// console.log('Full uncheck');
			$('input[name="gudang_sel[]"]').prop('checked', false);
		}

		$('input[name="gudang_sel[]"]').change();
	});


	// tombol cari
	$('.btnKirimPemeriksa').click(function (e) {
		e.preventDefault();
		e.stopPropagation();

		// also do nothing if no gudang is selected
		var selCount = $('input[name="gudang_sel[]"]:checked').length;

		if (selCount < 1) {
			alert("Tidak ada gudang yang dipilih!");
			return false;
		}

		// do nothing if dialog is opened
		if ($('#dlgPemeriksa').is(':visible')) {
			// alert('Tutup dulu oi');
			return false;
		}

		var type = $(this).data('type');
		var url = $(this).data('url');

		// grab selected gudang
		var listGudang = '';

		$('input[name="gudang_sel[]"]:checked').each(function() {
			if (listGudang.length)
				listGudang += ','+$(this).val();
			else
				listGudang = $(this).val();
		});

		// alert(listGudang);
		$('#lokasiPenugasan').val(listGudang);
		$('#spPemeriksa').text(type);

		// grab available pemeriksa
		showBlocker("Mencari pemeriksa yang siap bertugas...");

		// alert(url);

		$.ajax({
			url: url,
			type: 'GET',
			processData: false,
			contentType: false,

			success: function(data, responseText, jqXHR) {
				// console.log(data);
				// console.log(data.pemeriksa.length);
				// check data
				
				// ada pemeriksa
				clearTabelPemeriksa();

				var cnt = 0;
				for (pemid in data.pemeriksa) {
					addRowPemeriksa(data.pemeriksa[pemid].id, data.pemeriksa[pemid].fullname);
					// console.log(data.pemeriksa[pemid].id + '  ' + data.pemeriksa[pemid].fullname);

					cnt++;
				}

				// ada beneran kagak?
				if (cnt == 0) {
					$('#pNullMsg').show();
					$('#tblPemeriksaSiap').hide();

					// disable tombol
					$('#btnKirimPemeriksa').prop('disabled', true);
				}
				else {
					$('#pNullMsg').hide();
					$('#tblPemeriksaSiap').show();

					// disable tombol
					$('#btnKirimPemeriksa').prop('disabled', false);
				}
				
				hideBlocker();
			},

			error: function(jqXHR, responseText, errorObj) {
				hideBlocker();
			}
		});

		

		$('#dlgPemeriksa').fadeIn();
	});

	// tombol tutup form pemeriksa
	$('#btnCloseForm').click(function(e) {
		e.preventDefault();
		e.stopPropagation();

		$('#dlgPemeriksa').fadeOut();
	});

	// behavior buat checkbox grup pemeriksa
	$('#selPemeriksa').click(function() {
		$('#tblPemeriksaSiap input[name="pemeriksa[]"]').prop('checked', $(this).prop('checked'));
	});

	// behavior buat checkbox per pemeriksa
	$('#tblPemeriksaSiap').on('click', 'input[name="pemeriksa[]"]', function() {
		updateGroupPemeriksaCheckBox();
	});

	// pas klik submit

	// pas submit kirim pemeriksa
	$('#frmKirimPemeriksa').submit(function(e) {
		e.preventDefault();
		e.stopPropagation();

		var selPemeriksa = $(this).find('input[name="pemeriksa[]"]:checked').length;

		if (!selPemeriksa) {
			alert("Pilih pemeriksa untuk dikirim!");
			return false;
		}

		if (!confirm("Kirim Pemeriksa?"))
			return false;

		// lanjut
		// alert("Oi");
		var frm = this;

		$.ajax({
			url: frm.action,
			data: new FormData(frm),
			type: 'POST',
			processData: false,
			contentType: false,

			success: function(data, status, jqXHR) {
				if (typeof data.error === 'undefined') {
					// no error
					alert("Pengiriman pemeriksa berhasil!");

					// klik tombol hide
					$('#btnCloseForm').click();
					// klik tombol cari
					$('#frmBrowseOutstanding').submit();
				} else {
					// error shieet
					alert("Error: " + data.error);
				}

				hideBlocker();
			},

			error: function(jqXHR, status, errorObj) {
				alert('Serious Error: ' + jqXHR.statusText);
				// console.log(jqXHR);
				hideBlocker();
			}
		});
	});

	// pas nampilin detail
	$('#cbShowDetailPemeriksa').change(function() {
		var show = $(this).prop('checked');

		if (show) {
			// alert('Tampilkan detil plis');

			// url status pemeriksa
			var url = $(this).data('url') + '/' + $('#gudang').val();

			var me = this;

			// alert(url);
			showBlocker("Mengambil status terkini pemeriksa...");
			$.get(url, function(data) {
				// console.log(data);

				clearDetailPemeriksa();
				updateDetailPemeriksa(data);

				// sembunyiin span?
				$('.pemPIB').closest('td').find('span').hide();
				$('.pemCNPIBK').closest('td').find('span').hide();

				// mnculin list
				$('.pemPIB').show();
				$('.pemCNPIBK').show();
			})
				.fail(function() {
					alert("Gagal menampilkan detil pemeriksa");
					// batal cek
					$(me).prop('checked', false);
				})
				.always(function() {
					hideBlocker();
				});
		}
		else {
			// alert('Sembunyikan detil plis');
			// refresh aja
			// $('#frmBrowseOutstanding').submit();

			// munculin span
			$('.pemPIB').closest('td').find('span').show();
			$('.pemCNPIBK').closest('td').find('span').show();

			// smbunyiin list
			$('.pemPIB').hide();
			$('.pemCNPIBK').hide();
		}
	});
});
</script>