<?php
if (!isset($listGudang))
	$listGudang = array();
?>
<div>
	<h2>
		Status Pemeriksa
	</h2>
	<button class="shAnim commonButton blueGrad" id="btnRefresh" data-url="<?php echo base_url('pemeriksa/querystatus');?>">Refresh</button>
	<hr>
	<p>
		Tampilkan
		<select class="styled" id="selView">
			<option value="all" selected>Semua</option>
			<option value="busy">Busy</option>
			<option value="available">Available</option>
		</select>
	</p>

	<div style="display: none;" id="protoGudang">
		<table class="table" id="protoTblPemeriksa">
			
			<tbody>
				<tr data-id="" data-type="">
					<td>
						<span class="rownum">1</span>
					</td>
					<td class="fullname">Pemeriksa #1</td>
					<td class="role">PIB,CN_PIBK</td>
					<td>
						<input type="text" name="pemeriksa[1][status]" class="stat_desc" value="BUSY" readonly />
					</td>
					<td style="min-width: 200px;">
						<select multiple="multiple" name="pemeriksa[1][lokasi]" class="lokasi_pemeriksa" >
							<option value="GBDL" >GBDL</option>
							<option value="GDHL" selected>GDHL</option>
						</select>
					</td>
					<td class="stat_time">10/01/2018 01:02</td>
					<td>
						<!-- <button class="shAnim commonButton blueGrad rowBtnUpdate" >Update</button> -->
						<button class="shAnim commonButton redGrad rowBtnCabut" >Siap</button>
						<button class="shAnim commonButton greenGrad rowBtnPulang" >Pulang</button>
					</td>
					<td>
						<input type="checkbox" class="ifullblock pointable marker" />
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<form id="frmStatusPemeriksa" action="<?php echo base_url('pemeriksa/simpanstatus');?>" method="POST">
		<table class="table" id="tblPemeriksa">
			<thead>
				<tr>
					<th>No</th>
					<th>Nama Pemeriksa</th>
					<th>Role</th>
					<th>Status</th>
					<th>Lokasi</th>
					<th>Sejak</th>
					<th>
						Action
					</th>
					<th>
						<input type="checkbox" class="ifullblock pointable" id="selPemeriksa"/>
					</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

		<hr>

		<div>
			<button class="shAnim commonButton blueGrad" id="btnSimpan">Simpan</button>
			<button class="shAnim commonButton redGrad" id="btnCabut">Siap Ditugaskan</button>
			<button class="shAnim commonButton greenGrad" id="btnPulang">Absen Pulang</button>

			<!-- <button class="shAnim commonButton redGrad" id="btnTest">Test</button> -->
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
// multiselect
var ms_settings = {
	columns : 2,
	search : true,
	selectAll : true,
	texts : {
		placeholder: '',
		search : 'Ketik gudang yang dicari'
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

// clear table
function clearTable() {
	$('#tblPemeriksa tbody tr').remove();
}

function addRow(userid, fullname, role, a_location, stat_desc, status, stat_time) {
	// first, remove checked attrib from prototype
	$('#protoTblPemeriksa .lokasi_pemeriksa option').prop('selected', false);

	// now, clone our shit
	var row = $('#protoTblPemeriksa tbody tr:first').clone();

	var rowCount = $('#tblPemeriksa tbody tr').length;

	// fill in data
	row.find('.rownum').text(rowCount+1);

	row.data("id", userid);
	row.data("type", status);
	row.attr("data-id", userid);
	row.attr("data-type", status);
	

	row.find('.fullname').text(fullname);

	row.find('.role').text(role);
	// row.find('.stat_desc').text(stat_desc);
	row.find('.stat_desc')
		.val(status)
		.attr('name', 'pemeriksa['+userid+'][status]')
	row.find('.stat_time').text(stat_time);

	// hardest part, mark each selected shits
	for (var i=0; i<a_location.length; i++) {
		// row.find('option[value=" '+a_location[i]+' "]').attr('selected', true);

		var opt = row.find('option[value="' +a_location[i]+ '"]');
		if (opt.length > 0)
			opt[0].selected = true;
		// console.log(a_location[i]);
		// console.log(opt);
	}

	// do multiselect

	row.find('.lokasi_pemeriksa')
		.attr('name', 'pemeriksa['+userid+'][lokasi][]')
		.prop('disabled', status === 'NON-AKTIF')
		.multiselect(
		ms_settings
		)
		;

	// append that shit
	$('#tblPemeriksa tbody').append(row);
}

function updateGudang(gudang) {
	$('#protoTblPemeriksa .lokasi_pemeriksa option').remove();

	for (var i=0; i<gudang.length; i++) {
		var opt = $("<option>", {value: gudang[i], text: gudang[i]});

		$('#protoTblPemeriksa .lokasi_pemeriksa').append(opt);
	}
}

function updatePemeriksa(pemeriksa) {
	clearTable();

	// now add row
	for (userid in pemeriksa) {
		addRow(
			userid, 
			pemeriksa[userid].fullname, 
			pemeriksa[userid].role,
			pemeriksa[userid].lokasi, 
			pemeriksa[userid].status_desc,
			pemeriksa[userid].status,
			pemeriksa[userid].stat_time
			);
	}
}

function updatePage(data) {
	updateGudang(data.gudang);
	updatePemeriksa(data.pemeriksa);
	updateGroupCheckBox();

	$('#selView').change();
}

function updateGroupCheckBox() {
		var cbCount = parseInt( $('#tblPemeriksa .marker').length );
		var cbSelCount = parseInt( $('#tblPemeriksa .marker:checked').length );

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

// page behavior
$(function() {
	

	// var opt = $("<option>", {value: "AAA", text: "SHHH"});

	// console.log(opt);

	var lastData = null;

	// button refresh
	$('#btnRefresh').click(function(e) {
		showBlocker("Querying status pemeriksa...");

		var url = $(this).data('url');

		// var me = this;

		$.ajax({
			url: url,
			type: 'GET',
			success: function(data, status, jqXHR) {
				hideBlocker();

				// console.log(data);
				updatePage(data);

			},
			error: function(jqXHR, status, errorObj) {
				hideBlocker();

				alert('Error: ' + jqXHR.statusText);
			}
		});
	}).click();

	// view change
	$('#selView').change(function() {
		var value = $(this).val();

		if (value == 'all') {
			// show all
			$('#tblPemeriksa tbody tr').show();
		} else if (value == 'busy') {
			$('#tblPemeriksa tbody tr').hide();
			$('#tblPemeriksa tbody tr[data-type="BUSY"]').show();
		} else {
			$('#tblPemeriksa tbody tr').hide();
			$('#tblPemeriksa tbody tr[data-type="AVAILABLE"]').show();
		}

		// renumber
		$('tr:visible span.rownum').map( (x,v) => {v.innerText = (x+1);} );
	});

	// button update
	$('#tblPemeriksa').on('click', '.rowBtnUpdate', function(e) {
		e.preventDefault();
		e.stopPropagation();
		alert("Clicked id: " + $(this).closest('tr').data('id'));
	});

	// button pulang
	$('#tblPemeriksa').on('click', '.rowBtnPulang', function(e) {
		e.preventDefault();
		e.stopPropagation();
		// simply set to busy, + 
		// $(this).closest('tr').find('.status').val('HOME');
		$(this).closest('tr')
			.find('.stat_desc')
			.val('NON-AKTIF');

		$(this).closest('tr')
			.find('.lokasi_pemeriksa')
			.val('')
			.prop('disabled', true)
			.multiselect('reload');
	});

	// button cabut
	$('#tblPemeriksa').on('click', '.rowBtnCabut', function(e) {
		e.preventDefault();
		e.stopPropagation();

		$(this).closest('tr')
			.find('.stat_desc')
			.val('AVAILABLE');

		$(this).closest('tr')
			.find('.lokasi_pemeriksa')
			.val('')
			.prop('disabled', false)
			.multiselect('reload');
	});

	// ketika checkbox terpilih
	$('#tblPemeriksa').on('change', '.marker', function() {
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
	$('#selPemeriksa').change(function() {
		if ($(this).prop('checked')) {
			// full check
			// console.log('Full check');
			$('#tblPemeriksa .marker').prop('checked', true);
		} else {
			// full uncheck
			// console.log('Full uncheck');
			$('#tblPemeriksa .marker').prop('checked', false);
		}

		$('.marker').change();
	});

	// cabut smua
	$('#btnCabut').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		// semua yg ada di situ di klik aje
		$('#tblPemeriksa .marker:checked')
			.closest('tr')
			.find('.rowBtnCabut')
			.click();
	});

	// pulangin smua
	$('#btnPulang').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		// semua yg ada di situ di klik aje
		$('#tblPemeriksa .marker:checked')
			.closest('tr')
			.find('.rowBtnPulang')
			.click();
	});

	// kalo lokasi diisi, otomatis langsung flag busy
	$('#tblPemeriksa').on('change', '.lokasi_pemeriksa', function(e) {
		// console.log(e);
		// alert($(this).val());
		if ($(this).val() !== null) {
			// ada isi, busy
			$(this).closest('tr')
				.find('.stat_desc')
				.focus()
				.val('BUSY')
				.blur();
		} else {
			// null, otomatis cabut
			$(this).closest('tr')
				.find('.stat_desc')
				.focus()
				.val('AVAILABLE')
				.blur();
		}
	});

	// tombol simpan di klik
	$('#btnSimpan').click(function(e) {
		return confirm('Simpan status pemeriksa?');
	});

	// form di submit
	$('#frmStatusPemeriksa').submit(function(e) {
		// prevent page refresh
		e.preventDefault();
		e.stopPropagation();

		// kirim via ajax
		showBlocker("Menyimpan status pemeriksa...");

		var frm = this;
		var fd = new FormData(this);

		$.ajax({
			url: frm.action,
			type: 'POST',
			data: fd,
			processData: false,
			contentType: false,
			
			success: function(data, status, jqXHR) {
				// just say it tho
				if (data) {
					alert("Status tersimpan");
					// refresh
					$('#btnRefresh').click();

					// console.log(data);
				} else {
					alert("Gagal menyimpan status");
				}
				hideBlocker();
			},
			error: function(jqXHR, status, errorObj) {
				alert("Error: " + jqXHR.statusText);
				hideBlocker();
			}
		});
	});

	/*// test
	$('#btnTest').click(function(e) {
		e.preventDefault();
		e.stopPropagation();

		// let's store some shit
		var data = {};

		// add lokasi
		$('#frmStatusPemeriksa .lokasi_pemeriksa').each(function() {
			// append this shit
			var name = this.name;
			var value = $(this).val();

			// console.log(this);
			// console.log(value);

			data[name] = value;
		});

		// add status
		$('#frmStatusPemeriksa input').each(function() {
			// append this shit too
			data[this.name] = $(this).val();
		});

		console.log(data);

		// test send
		$.ajax({
			url: 'http://192.168.146.55/sapi/app/echoresponse',
			type: 'POST',
			data: data,
			processData: false,
			contentType: false,
			success: function(a, b, c) {
				console.log(a);
			},

			error: function(a, b, c) {
				console.log(b);
			}
		});
	});*/
});
</script>