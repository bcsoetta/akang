<?php
if (!isset($tglAbsen))
	$tglAbsen = date('d/m/Y');
?>
<div>
	<form id="frmAbsenPemeriksa" action="<?php echo base_url('pemeriksa/simpanabsen');?>" method="POST">
		<h2>Tanggal Absen</h2>
		<input class="datepicker shAnim" type="text" id="tglAbsen" name="tglAbsen" value="<?php echo $tglAbsen;?>" />
		<button class="shAnim commonButton blueGrad" id="btnCariAbsen" data-url="<?php echo base_url('pemeriksa/statusabsen/');?>">Cari</button>
		<hr>

	
		<h2>Daftar Pemeriksa Barang</h2>
		<table class="table" id="tblPemeriksa">
			<thead>
				<tr>
					<th rowspan="2" style="vertical-align: middle; width: 10%;">No</th>
					<th rowspan="2" style="vertical-align: middle;">Nama</th>
					<th colspan="2">Role</th>
				</tr>
				<tr>
					<th style="width: 10%;">
						<label class="pointable ifullblock">PIB
						<!-- <input type="checkbox" id="cbPIB" /> -->
						</label>
					</th>
					<th style="width: 10%;">
						<label class="pointable ifullblock">CN/PIBK
						<!-- <input type="checkbox" id="cbCNPIBK" /> -->
						</label>
					</th>
				</tr>
			</thead>	
			<tbody>
				
			</tbody>
		</table>
		<hr>
		<div>
			<input type="submit" class="shAnim commonButton redGrad" value="Simpan" />
		</div>
	</form>
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


// bersihin tabel
function clearTable() {
	$('#tblPemeriksa tbody tr').remove();
}
// nambah entry di tabel
function addRow(userid, fullname, as_pib, as_cnpibk) {
	var rowCount = $('#tblPemeriksa tbody tr').length;

	var row = "<tr>"
			+ "<td>" + (rowCount+1) + '<input type="hidden" name="pemeriksa['+ userid +'][role]" />' + "</td>"
			+ "<td>" + fullname + "</td>"
			+ "<td>" + '<input type="checkbox" class="pointable ifullblock" value="PIB" name="pemeriksa[' + userid +'][role][]" ' + (as_pib?'checked':'') +'/>' + "</td>"
			+ "<td>" + '<input type="checkbox" class="pointable ifullblock" value="CN_PIBK" name="pemeriksa[' + userid +'][role][]" ' + (as_cnpibk?'checked':'') + '/>' + "</td>"
			+ "</tr>";

	$('#tblPemeriksa tbody').append(row);
}

// page specific behavior
$(function() {

	// pas klik cari absen. also click when page first loads
	$('#btnCariAbsen').click(function(e) {
		e.preventDefault();
		e.stopPropagation();

		showBlocker("Ambil data absen...");
		var url = $(this).data('url')+$('#tglAbsen').val();
		// hideBlocker();

		// addRow(6, 'Pemeriksa #1', true, false);
		var jqXHR = $.get(url, function(data) {
			// console.log(data);
			clearTable();

			for (var i=0; i<data.pemeriksa.length; i++) {
				addRow(data.pemeriksa[i].id, data.pemeriksa[i].fullname, data.pemeriksa[i].as_pib, data.pemeriksa[i].as_cnpibk);
			}

			// fix date?
			$('#tglAbsen').val(data.tanggal_absen);
		})
			.fail(function() {
				alert("Error getting shiets done. " + jqXHR.responseText);
			})
			.always(function() {
				hideBlocker();
			});
	}).click();

	$('#frmAbsenPemeriksa').submit(function(e) {
		// stop page refresh
		e.preventDefault();
		e.stopPropagation();

		// grab data
		var frm = this;

		var fd = new FormData(frm);

		// send
		showBlocker('Menyimpan Absen...');

		$.ajax({
			url: frm.action,
			data: fd,
			type: 'POST',
			processData: false,
			contentType: false,

			success: function(data, status, jqXHR) {
				if (data.result) {
					alert("Absen tanggal "+data.tglAbsen+" telah tersimpan.");
				} else {
					alert("Absen gagal tersimpan!");
				}
				hideBlocker();
			},
			error: function(jqXHR, status, errorObj) {
				alert("Error: " + jqXHR.responseText);
				hideBlocker();
			}
		});
	});
});

</script>