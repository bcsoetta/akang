<?php
if (!isset($datestart))
	$datestart = date('d/m/Y');
if (!isset($dateend))
	$dateend = date('d/m/Y');
if (!isset($id_pemeriksa))
	$id_pemeriksa = 0;

$listGudang = $listGudang ?? ['GJDC','GIBS'];
?>
<div id="toolbox">
	<form action="<?php echo base_url('app/query/bap');?>" id="parambox">
		<p>
			<span class="field">Tanggal BAP</span>
			<input id="datestart" class="datepicker shAnim shInput" name="tanggal" value="<?php echo $datestart;?>" type="text" />
			
			<span class="field">Keyword</span>

			<input class="shAnim si2 tooltip" name="keyword" value="" type="text" />

			<input class="commonButton blueGrad shAnim" name="submit" value="Cari" type="submit" />
			<input class="commonButton redGrad shAnim" value="Kosongkan" type="reset" />
			<input type="hidden" name="pageid" value="1" />
			<input type="hidden" name="pemeriksaid" value="<?php echo $id_pemeriksa;?>" />
		</p>

		<p class="right" style="margin: 12px auto;">
			<span class="field">Tampilkan </span>
			<select class="styled shAnim" name="itemperpage" id="itemPerPage">
				<option value="5" selected>5 Item</option>
				<option value="10" selected>10 Item</option>
				<option value="25">25 Item</option>
				<option value="50">50 Item</option>
				<option value="100">100 Item</option>
			</select>
		</p>
	</form>
</div>
<div>
	<button 
		class="commonButton blueGrad shAnim" 
		id="btn-dlg-bap"
		style="display: inline-block;">Rekam BAP</button>
</div>
<div>
	<table class="table" id="tblBatch">
		<thead>
			<tr>
				<th>No.</th>
				<th>Nomor BAP</th>
				<th>Tanggal BAP</th>
				<th>PJT</th>
				<th>Total HAWB diperiksa</th>
				<th>Lokasi</th>
				<th>Action</th>
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
		Loading Data...
		</h2>
		<div id="spinnerBox">
			<div class="spinner">
			</div>
		</div>
	</div>
</div>


<div class="floatForm" id="dlg-bap">
	<h2>
	Rekam Berita Acara Pemeriksaan
	</h2>

	

	<form id="frmBrowseFinished" action="<?php echo base_url('app/query/finished');?>">

	<p>
		<span>Tanggal BAP</span>
		<input id="dateend" class="datepicker shAnim shInput" name="tanggal" value="<?php echo $datestart;?>" type="text" />
	</p>

	<p>
		<span>Gudang</span>

		<select id="gudang" name="gudang[]" multiple>
		

		<?php
		foreach ($listGudang as $gudang) {
		?>

		<option value="<?php echo $gudang;?>"><?php echo $gudang;?></option>

		<?php
		}
		?>

		</select>
	</p>
	
	

	<p>
	<input type="hidden" name="pemeriksaid" value="<?php echo $id_pemeriksa;?>" />
	<input type="submit" class="commonButton blueGrad shAnim" value="Cari"/>
	</p>

	</form>

	<hr>

	<div style="overflow-y: auto; height: 175px;">
		<table class="table" id="tblFinished">
			<thead>
				<tr>
					<th>No.</th>
					<th>PJT</th>
					<th>GUDANG</th>
					<th>Total HAWB</th>	
					<th></th>	
				</tr>
			</thead>

			<tbody>
				
			</tbody>
		</table>
	</div>

	<!-- <hr> -->
	<p style="text-align: right; margin-top: 0.75em;">
		<!-- <button type="submit" class="commonButton redGrad shAnim" id="btnKirimPemeriksa">Kirim</button> -->
		<button type="button" class="commonButton redGrad shAnim btnFloatClose" id="btnCloseForm">Tutup</button>
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

function clearFinishedTable() {
	$('#tblFinished tbody tr').remove();
}

function addRowTblFinished(pjt, gudang, total_hawb, url) {
	var rowCount = $('#tblFinished tbody tr').length;

	var row = 
	`<tr>
		<td class="rowNum">${rowCount+1}</td>
		<td>${pjt}</td>
		<td>${gudang}</td>
		<td>${total_hawb}</td>
		<td>
			<button class="commonButton blueGrad shAnim btnRekamBap" data-url="${url}">Rekam</button>
		</td>
	</tr>`;

	$('#tblFinished tbody').append(row);
}

function addRow(bap_no, tgl_formatted, pjt, total_hawb, lokasi, url) {
	var rowCount = $('#tblBatch tbody tr').length;

	var row = '<tr>';

	// nomor
	row += '<td class="rowNum">' + (pagingData.startNumber+rowCount+1) + '</td>';
	// bap number
	row += '<td>' + bap_no + '</a></td>';
	// tgl dok
	row += '<td>' + tgl_formatted + '</td>';
	// waktu upload
	row += '<td>' + pjt + '</td>';
	// uploader
	row += '<td>' + total_hawb + '</td>';
	// lokasi
	row += '<td>' + lokasi + '</td>';

	row += `<td><div><a href="${url}" target="_blank" style="display: inline-block; margin: 0.2em;" class="commonButton blueGrad shAnim">Cetak</a></div></td>`;

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
	// multiselect
	$('select#gudang').multiselect({
		columns : 4,
		search : true,
		selectAll : true,
		texts : {
			placeholder: 'Pilih gudang',
			search : 'Ketik gudang yang dicari'
		}
	});


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

	// behavior utk form cari finished
	$('#frmBrowseFinished').submit(function (e) {
		e.preventDefault();
		e.stopPropagation();

		var frm = this;
		var fd = new FormData(frm);

		console.log(fd);

		var validated = $('select[name="gudang[]"]').val() !== null
						&& $('input[name="dateend"]').val() != '';

		if (!validated) {
			alert("Parameter pencarian tidak lengkap!");
			return false;
		}

		showBlocker("Cari CN yang belum di BAP...");

		clearFinishedTable();

		$.ajax({
			url: frm.action,
			type: 'POST',
			data: fd,
			cache: false,
			processData: false,
			contentType: false,

			success: function(data, textStatus, xhr) {
				console.log(data);

				for (var i=0; i<data.length; i++) {
					const d = data[i];

					addRowTblFinished(d.fullname, d.gudang, d.total, d.url);
				}

				hideBlocker();
			},

			error: function(xhr, textStatus, error) {
				alert(error);
				hideBlocker();
			}
		});
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
							data.data[i].nomor_lengkap,
							data.data[i].tanggal_formatted,
							data.data[i].pjt,
							data.data[i].total_hawb,
							data.data[i].gudang,
							data.data[i].url
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


	$('#btn-dlg-bap').click(function (e) {
		$('#dlg-bap').show(200);
	});

	$('#btnCloseForm').click(function (e) {
		$('#dlg-bap').hide(200);
	});

	// utk tombol rekam bap
	$('body').on('click', '.btnRekamBap', function (e) {
		// alert(this);
		const url = $(this).data('url');
		// alert(url);
		showBlocker("Merekam BAP...");
		/* setTimeout(() => {
			hideBlocker();
			clearFinishedTable();
			$('#dlg-bap').hide(200);
		}, 2000); */
		$.get(url, (data, textStatus, xhr) => {
			
		})
		.done(() => {
			// clearFinishedTable();
			$('form#frmBrowseFinished').submit();
			$('#dlg-bap').hide(200);
		})
		.fail(() => {
			alert("Gagal rekam BAP");
			// $('#dlg-bap').hide(200);
		})
		.always(() => {
			hideBlocker();
			$('form#parambox').submit();
		});
	});
});
</script>