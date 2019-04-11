<?php
if (!isset($result))
	$result = array(
		'no_dok' => '',
		'response' => array()
	);

$canFinish = isset($canFinish) ? $canFinish : false;
$canOvertime = isset($canOvertime) ? $canOvertime : false;
$adminMode = isset($adminMode) ? $adminMode : false;


?>

<div id="searchbox">
	<form id="frmSearch" action="" method="GET">
		<p>No. Barang Kiriman / CARNET</p>
		<div>
			<input type="text" name="hawb" class="si shAnim" value="<?php echo isset($hawb)?$hawb:''; ?>" placeholder="Masukkan no barang kiriman/PIB..." list="awbList" id="awbQuery">
				<datalist id="awbList" data-src="<?php echo base_url('app/query/awb');?>" data-cache=""></datalist>
			<input type="submit" value="Cari" class="shAnim commonButton blueGrad">
		</div>
		<p>
			<span class="spinner24" id="spinner"></span>
		</p>
	</form>

	
</div>

<?php
// when hawb is set, search is started
if (isset($hawb)) {
?>

<hr>

<?php
	if (count($result['response']) == 0 ) {
?>

<p style="text-align: center;">
	Data tidak ditemukan
</p>

<?php
	} else {
?>
<p>
	<?php
	if ($result['response'][0]['jenis_dok'] == 'CARNET')
		echo 'NO CARNET ';
	else
		echo 'NO BRG KIRIMAN ';
	?>
	 <?php echo $hawb . ' / ' . $result['consignee'];?>
</p>

<table class="table">
	<thead>
		<th>Status</th>
		<th>Waktu</th>
		<th>Operator</th>
		<th>Catatan</th>
	</thead>
	<tbody>
		<?php
		foreach ($result['response'] as $resp) {
			
		?>

		<tr>
			<td><?php echo $resp['status'] ?></td>
			<td><?php echo $resp['time_formatted'] ?></td>
			<td><?php echo $resp['fullname'] ?></td>
			<td><?php echo $resp['catatan'] ?></td>
		</tr>

		<?php
		}
		?>
	</tbody>
</table>

<?php
	}
}

if ($adminMode) {
?>

<hr>
<div id="act">
	<button data-url="<?php echo base_url('pemeriksa/flag/selesai');?>" data-dokumen="<?php echo $result['dok_id'];?>" class="commonButton blueGrad shAnim" <?php if (!$canFinish) echo 'disabled';?>>Selesaikan</button>

	<button data-url="<?php echo base_url('pemeriksa/flag/overtime');?>" data-dokumen="<?php echo $result['dok_id'];?>" class="commonButton redGrad shAnim" <?php if (!$canOvertime) echo 'disabled';?> >Flag Overtime</button>
</div>

<?php
}
?>


<!-- BLOCKER -->
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

$(function() {
	// hide spinner
	$('#spinner').hide();
	// for the timer
	var timer = null;

	// how many seconds before querying
	var secLapse = 1000;

	// last search
	var lastSearch = '';

	// start seach length
	var startLength = 3;

	// what happens when the user finishes typing
	$('#awbQuery').keyup(function(e) {
		// ignore if directional keys
		if (e.keyCode >= 37 && e.keyCode <= 40 || e.keyCode < 13)
			return true;

		// console.log(e);

		var text = $(this).val();


		// console.log(cache);

		if (text.length == startLength) {
			// 
			// console.log(lastSearch + " - " + text);

			// create timer
			if (lastSearch !== text)
				queryAWB();
				// timer = setTimeout(queryAWB, secLapse);
			// console.log(timer);

			// store cache
			lastSearch = text;
		}

		return true;
	});

	// when a button is click
	$('#act button').click(function(e) {
		// alert();
		e.preventDefault();
		e.stopPropagation();

		var completeURL = $(this).data('url') + '/' + $(this).data('dokumen');

		// get ajax
		showBlocker("Flag dokumen selesai...");
		var jqXHR = $.get(completeURL, function(data) {
			// just click teh search button again
			$('#frmSearch').submit();
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

	// the real shit
	function queryAWB() {
		// $('#awbQuery').trigger("keydown", {which: 13, keyCode: 13});
		$('#spinner').show();

		var szHawb = $('#awbQuery').val();
		// console.log("Do lookup: " + szHawb);

		//
		$.ajax({
			url: $('#awbList').data('src'),
			type: 'GET',
			data: {
				hawb: szHawb
			},

			success: function(data, error, jqXHR) {
				$('#spinner').hide();

				console.log(data);

				if (data.status == 'success') {
					// build shit
					var htmlDL = '';

					for (var i=0; i<data.result.length; i++) {
						htmlDL += '<option>' + data.result[i] + '</option>';
					}

					$('#awbList').empty();
					$('#awbList').html(htmlDL);

							
					$('#awbQuery').blur();
					$('#awbQuery').focus();
					$('#awbQuery').click();
					
					$('#awbQuery').trigger(jQuery.Event('keypress', { keyCode: 40, which: 40 }));
					$('#awbQuery').trigger(jQuery.Event('keydown', { keyCode: 40, which: 40 }));
					$('#awbQuery').trigger(jQuery.Event('keyup', { keyCode: 40, which: 40 }));
					// pencet_tombol($('#awbQuery')[0], 65);
				}

				
				// $('#awbQuery')[0].dblclick();

			},

			error: function(jqXHR, error, obj) {
				$('#spinner').hide();
				console.log(error);
			}
		});
	}
});
</script>
