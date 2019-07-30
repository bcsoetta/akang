<?php
if (!isset($datestart))
	$datestart = date('d/m/Y');
if (!isset($dateend))
	$dateend = date('d/m/Y');
?>
<div id="toolbox">
	<form action="<?php echo base_url('app/query/request');?>" id="parambox">
		<p>
			<span class="field">Tanggal Permohonan</span>
			<input id="datestart" class="datepicker shAnim shInput" name="datestart" value="<?php echo $datestart;?>" type="text" />
			<span>s/d&nbsp;</span>
			<input id="dateend" class="datepicker shAnim shInput" name="dateend" value="<?php echo $datestart;?>" type="text" />
			<select class="styled shAnim" name="paramtype">
				<option value="batchid" selected>No. Batch</option>
				<option value="lokasi">Lokasi Barang</option>

				<?php
				if (isset($adminMode)) {
				?>
				
				<option value="uploader">Uploader</option>
				
				<?php
				}
				?>
			</select>

			<input class="shAnim si2 tooltip" name="paramvalue" value="" type="text" />

			<span class="field">Jenis Pengajuan</span>
			<select class="styled shAnim" name="doctype">
				<option value="ALL" selected>Semua</option>
				<option value="PIB">PIB</option>
				<option value="CN_PIBK">CN/PIBK</option>
				<option value="CARNET">CARNET</option>
			</select>

			<input class="commonButton blueGrad shAnim" name="submit" value="Cari" type="submit" />
			<input class="commonButton redGrad shAnim" value="Kosongkan" type="reset" />
			<input type="hidden" name="pageid" value="1" />
		</p>

		<p class="right" style="margin: 12px auto;">
			<span class="field">Tampilkan </span>
			<select class="styled shAnim" name="itemperpage" id="itemPerPage">
				<option value="5" selected>5 Item</option>
				<option value="10" selected>10 Item</option>
				<option value="25">25 Item</option>
				<option value="25">50 Item</option>
				<option value="25">100 Item</option>
			</select>
		</p>
	</form>
</div>
<div>
	<table class="table" id="tblBatch">
		<thead>
			<tr>
				<th>No.</th>
				<th>Batch #</th>
				<th>Jenis Dok</th>
				<th>Waktu Upload</th>
				<th>Uploader</th>
				<th>Lokasi</th>
				<th>Penyelesaian</th>
			</tr>
		</thead>
		<tbody>
			
		</tbody>
	</table>

	<div id="pagingbox">
		<button id="btnFirst" class="commonButton redGrad shAnim disabled">&lt;&lt;</button>
		<button id="btnPrev" class="commonButton redGrad shAnim disabled">&lt;</button>
		<input id="pageid" class="shAnim spinInput" value="1" type="text">
		<span id="totaldata">/ 0 [0]</span>
		<button id="btnNext" class="commonButton redGrad shAnim disabled">&gt;</button>
		<button id="btnLast" class="commonButton redGrad shAnim disabled">&gt;&gt;</button>

		<input type="hidden" value="" id="totalpage"/>
		<input type="hidden" value="" id="totalitem"/>
	</div>
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
var pagingData = {
	startNumber: 0,
	lastData : null
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

function clearTable() {
	$('#tblBatch tbody tr').remove();
}

function addRow(batchid, doctype, upload_time, uploader, lokasi, url, selesai, totalDok) {
	var rowCount = $('#tblBatch tbody tr').length;

	var row = '<tr>';

	// nomor
	row += '<td class="rowNum">' + (pagingData.startNumber+rowCount+1) + '</td>';
	// batch number
	row += '<td><a class="ifullblock" target="_blank" href="' + url + '" >' + batchid + '</a></td>';
	// jenis dok
	row += '<td>' + doctype + '</td>';
	// waktu upload
	row += '<td>' + upload_time + '</td>';
	// uploader
	row += '<td>' + uploader + '</td>';
	// lokasi
	row += '<td>' + lokasi + '</td>';

	// selesai
	var complete = (selesai / totalDok * 100.0).toFixed(2);

	row += '<td>' + selesai + '/' + totalDok + ' ('+complete + ' %' + ')' + '</td>';

	row += '</tr>';

	$('#tblBatch tbody').append(row);
}

function showPagingBox() {
	$('#pagingbox').show();
}

function hidePagingBox() {
	$('#pagingbox').hide();
}

function setPagingBox(pageid, totalpage, totalitem) {
	$('#totalpage').val(totalpage);
	$('#totalitem').val(totalitem);

	$('#pageid').val(pageid);
	$('#totaldata').text('/ ' + totalpage + ' [' + totalitem + ']');

	// enable all buttons
	$('#pagingbox button').prop('disabled', false);

	// analyze it
	if (pageid == 1)  {
		$('#btnFirst').prop('disabled', true);
		$('#btnPrev').prop('disabled', true);
	}

	if (pageid == totalpage) {
		$('#btnLast').prop('disabled', true);
		$('#btnNext').prop('disabled', true);
	}
}

function navigate(pageid, totalpage) {
	var currPage = Math.min( Math.max(pageid, 1), totalpage );
	// console.log('requested: ' + pageid + ', total: ' + totalpage + ', got: ' + currPage);

	$('#parambox input[name="pageid"]').val(currPage);
	$('#parambox').submit();
}

// setting behavior di sini
$(function() {
	// buat pas user awal2
	hidePagingBox();

	// behavior utk tombol navigasi
	$('#btnNext').click(function(e) {
		navigate( parseInt($('#pageid').val())+1, $('#totalpage').val());
	});

	$('#btnLast').click(function(e) {
		navigate( parseInt($('#totalpage').val()), $('#totalpage').val());
	});

	$('#btnPrev').click(function(e) {
		navigate( parseInt($('#pageid').val())-1, $('#totalpage').val());
	});

	$('#btnFirst').click(function(e) {
		navigate(1, $('#totalpage').val());
	});

	// behavior utk kolom navigasi pageId
	$('#pageid').keyup(function(e) {
		// console.log(e);
		if (e.keyCode == 13) {
			// enter nih
			var pageNum = $(this).val();

			// console.log(pageNum + ' / ' + pagingData.lastData.totalpage);

			navigate(pageNum, pagingData.lastData.totalpage);
		}
	});

	// behavior utk form pencarian
	$('#parambox').submit(function(e) {
		e.preventDefault();
		e.stopPropagation();

		// alert("Stop query puhlease");
		var frm = this;
		var fd = new FormData(this);

		showBlocker("Querying for data...");
		
		hidePagingBox();

		// console.log(this.action);
		$.ajax({
			url: frm.action,
			type: 'POST',
			data: fd,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data, status, jqXHR) {
				// let's write to table
				clearTable();
				
				if (typeof data.error === 'undefined') {
					console.log(data);
	
					// set startnumber
					pagingData.startNumber = (parseInt(data.pageid)-1) * parseInt(data.itemperpage);
					pagingData.lastData = data;
					

					for (var i=0; i<data.data.length; i++) {
						// batchid, upload_time, uploader, lokasi, url
						addRow(
							data.data[i].id,
							data.data[i].jenis_dok,
							data.data[i].time_formatted,
							data.data[i].fullname,
							data.data[i].gudang,
							data.data[i].url,
							data.data[i].total_finished,
							data.data[i].total_dok
							);
					}
					setPagingBox(data.pageid, data.totalpage, data.totaldata);
					showPagingBox();

				} else {
					// process error
					alert("ErrorData: " + data.error);
				}

				// hide blocker
				hideBlocker();
			},
			error: function(jqXHR, status, errorObj) {
				alert("Error("+jqXHR.status+"):\n"+jqXHR.statusText);
				console.log(jqXHR);
				console.log(jqXHR.responseText);
				// hide blocker
				hideBlocker();
				// clear table
				clearTable();
			}
		});
	});

	// behavior utk combo box jumlah item yang ditampilkan
	$('#itemPerPage').change(function() {
		$('#parambox').submit();
	});
});
</script>