<?php
$locked = isset($locked) ? $locked : false;
$docTypeName = $docType == 'CN_PIBK' ? 'CN/PIBK' : 'PIB';
?>

<div>
	<form id = "formExcelUpload" enctype="multipart/form-data" action="<?php echo base_url('app/parseinput')?>">
		<h2>
			Buat list dari file excel
		</h2>
		<p>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxFileSize;?>" />
			<input type="file" accept=".xls,.xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/csv" name="inputFile" class="shAnim" />
		</p>
		<p>
			<input type="submit" name="generate" value="Generate!" class="shAnim commonButton redGrad"/>
		</p>
	</form>
</div>
<hr/>
<div>
	<h2>Form Permohonan Pemeriksaan Fisik <?php echo $docTypeName;?></h2>
	<form id="formListPIB" enctype="multipart/form-data" method="POST" action="<?php echo base_url('app/processrequest/'.$docType);?>">
		<p>
			<span class="si">Lokasi Barang</span>
			<select class="shAnim styled" name="lokasi">
				<?php
				if (isset($listGudang)) {
					foreach ($listGudang as $gudang) {
				?>

				<option value="<?php echo $gudang;?>"><?php echo $gudang;?></option>

				<?php
					}
				}
				?>
				
			</select>
		</p>
		<hr/>
		<p>
			List <?php echo $docTypeName;?>
		</p>
		

			<table id="tblListPIB" class="table droppable">
				<thead>
					<tr>
						<th>No.</th>
						<th>Nomor <?php echo $docTypeName; ?></th>
						<th>Tanggal</th>
						<th><?php echo $docType=='CN_PIBK'?'Consignee':'Importir';?></th>

						<th>Jumlah Item</th>
						<th>Tonase (Kg)</th>

						<?php
						if ($docType == 'PIB') {
						?>

						<th>Foto Barang (Max 512Kb)</th>

						<?php
						}
						?>

						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<!-- HERE GOES THE LIST -->
				</tbody>
			</table>
			
		
	<hr/>
	<p>
		<input type="button" class="shAnim commonButton greenGrad" value="Tambah Dok" data-action="rowAdd" <?php if($locked)echo 'disabled' ?>/>
		<input type="button" class="shAnim commonButton blueGrad" value="Clear List" data-action="listClear" <?php if($locked)echo 'disabled' ?>/>
		<input type="submit" name="submit" class="shAnim commonButton redGrad submit" value="Kirim Permohonan" <?php if($locked)echo 'disabled' ?>/>
	</p>
	</form>

	<table style="display:none;" id="protoPIB">
		<tr>
			<td class="rowNum"></td>
			<td><input type="text" name="no_pib[]" class="shAnim <?php echo $docType == 'PIB'?'sixPad':'si'?>" value="" /></td>
			<td><input type="text" name="tgl_pib[]" class="tanggal shAnim" value="" /></td>
			<td><input type="text" name="importir[]" class="shAnim si importir"/></td>

			<td><input type="number" name="jml_item[]" step="1" min="0" class="shAnim"/></td>
			<td><input type="number" name="berat_kg[]" step="any" min="0" class="shAnim"/></td>
			<?php
			if ($docType == 'PIB') {
			?>

			<td>
				<input type="file" accept="image/jpeg,image/jpg,image/gif,image/png" class="imageBrowse"/>
				
					<p class="filename">
					</p>
					<img src="" class="imagePreview" style="display: none;">
				
				<input type="hidden" value="" name="img_name[]"/>
				<input type="hidden" value="" name="img_src[]"/>				
			</td>

			<?php
			}
			?>

			<td>
				<input type="button" class="shAnim commonButton redGrad" value="Delete" data-action="rowDelete"/>

				<?php
				if ($docType == 'PIB') {
				?>

				<input type="button" class="shAnim commonButton blueGrad" value="Clear Img" data-action="imgClear"/>

				<?php
				}
				?>

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
	// konfigurasi untuk halaman ini
	var config = {
		maxImageSize: 1024*1024		// max ukuran gambar 1Mb
	};

	// tambah row
	function addNewRow(no_pib, tgl_pib, importir, jml_item, berat_kg) {
		// pad no_pib
		if ($.isNumeric(no_pib)) {
			while (no_pib.length < 6)
				no_pib = '0'+no_pib;	
		}
		
		// set prototype data
		var rowCount = $('#tblListPIB tbody tr').length;

		// set nomor
		var rowId = (rowCount+1);
		$("#protoPIB .rowNum").text( rowId + "." );
		// $("#protoPIB .sixPad").val('23123');

		// copy html
		var html = $('#protoPIB tbody').html();

		// console.log(html);
		// tambah di table
		$('#tblListPIB tbody').append(html);

		// inisialisasi datepicker
		$("#tblListPIB tbody tr:last-child .tanggal").datepicker(datepickerOptions);
		$('#tblListPIB tbody tr:last-child input[name="no_pib[]"]').val(no_pib);
		$("#tblListPIB tbody tr:last-child .tanggal").val(tgl_pib);
		$('#tblListPIB tbody tr:last-child input[name="importir[]"]').val(importir);
		$("#tblListPIB tbody tr:last-child").attr("row_id", rowId);

		$('#tblListPIB tbody tr:last-child input[name="berat_kg[]"]').val(berat_kg);
		$('#tblListPIB tbody tr:last-child input[name="jml_item[]"]').val(jml_item);
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

	// cari row Id di tabel list PIB berdasarkan nomor
	function findRowId(no_pib) {
		// first, pad
		var pibPadded = no_pib;
		while (pibPadded.length < 6)
			pibPadded = '0'+pibPadded;

		// next, linear search (SLOOOW)
		var result = null;
		$.each($("#tblListPIB tbody").find('.sixPad'), function(k, v) {
			var nomor = $(v).val();
			// console.log(v.value);
			// console.log(nomor);

			// console.log("comparing: " + no_pib + ', ' + pibPadded + " vs. " + nomor);

			if (nomor == pibPadded || nomor == no_pib) {
				result = v.closest('tr');
				return result;
			}
		});
		return result;
	}

	// set gambar di row tertentu
	function setRowImage(row, image) {
		var reader = new FileReader();
		// console.log(image);
		reader.onload = function(e) {
			// console.log(e);
			$(row).find('.imagePreview').attr('src', e.target.result).show();
			$(row).find('.filename').text(image.name);
			$(row).find('.imageBrowse').hide();
			// $(row).find('.imgLink').attr('href', e.target.result);
		}

		// cek ukuran gambar, klo kegedean batal
		if (image.size > config.maxImageSize) {
			alert("Ukuran file gambar '" + image.name + "' melebihi batas!");

			// nullin lagi
			$(row).find('.imageBrowse').val('');
		} else {
			reader.readAsDataURL(image);
		}
	}

	// script ini lokal di view ini aja
	$(function() {
		// buat droppable element
		$.each($('.droppable'), function(id, elem) {
			$(elem).removeClass('dropactive');

			// pas di hover
			elem.addEventListener('dragover', function(e) {
				e.preventDefault();
				e.stopPropagation();

				$(elem).addClass('dropactive');

				return false;
				// console.log("Drag is happening");
			});

			// pas di leave
			elem.addEventListener('dragleave', function(e) {
				e.preventDefault();
				e.stopPropagation();

				$(elem).removeClass('dropactive');
				// console.log("Drag is over");

				return false;
			});

			// pas di drop
			elem.addEventListener('drop', function(e) {
				e.preventDefault();
				e.stopPropagation();

				$(elem).removeClass('dropactive');

				// console.log('dropped something.');
				// console.log(e.dataTransfer.files);

				var files = e.dataTransfer.files;

				for (var i=0; i<files.length; i++) {
					var file = files[i];

					var filename = file.name.trim();

					var matches = filename.match(/^(\d+)\.(png|jpg|jpeg|bmp|gif)$/i);

					// console.log(matches);
					if (matches.length >= 3) {
						// found a valid name
						
						// console.log("row search: " + matches[1]);
						// console.log('result: ');
						var rowId = findRowId(matches[1]);
						// console.log(rowId);

						// if it's not null, set teh image
						if (rowId) {
							// $(rowId).find('.imageBrowse').val(file);
							setRowImage(rowId, file);
						}
					} else {
						alert("Invalid filename for: " + filename);
					}
				}
				
			});
		});

		// behavior buat tombol tambah PIB
		$('body').on('click', 'input[data-action="rowAdd"]', function() {
			addNewRow('', '');
		});

		// behavior buat six padded input
		// validasi + padding
		$('body').on('blur', '.sixPad', function() {
			var content=$(this).val().trim();
			var valid = content.match(/^[0-9]{1,6}$/);
			if (!valid && !$(this).hasClass("redGrad")) {
				alert("Nomor PIB tidak valid!");
				$(this).addClass("redGrad");
			} else {
				$(this).removeClass("redGrad");
				// valid, kasih padding
				var len=content.length;
				for (var i=0; i<6-len; i++)
					content = '0'+content;

				$(this).val(content);
			}
		});

		// behavior buat tombol Delete Row
		$('body').on('click', 'input[data-action="rowDelete"]', function() {
			var tbody = $(this).closest('tbody');
			// konfirmasi
			if (/*confirm("Serius?")*/1) {
				// serius
				$(this).closest('tr').remove();

				// reorder nomor baris
				$.each($(tbody).find('tr'), function(k, v) {
					// console.log(k);
					// nomor rownya adalah k + 1 (karena 0 based index)
					// console.log($(this).closest('tr').find('.rowNum'));

					// set row Number
					$(this).closest('tr').find('.rowNum')[0].innerText = (k+1) + ".";
					// $(this).closest('tr').attr('row_id', null);
					$(this).closest('tr').attr('row_id', (k+1));
				});
			}
		});

		// behavior buat tombol clear list
		$('input[data-action="listClear"]').click(function(e) {
			if (confirm("Hapus semua Dokumen?")) {
				$("#tblListPIB tbody tr").remove();
			}
		});

		// behavior buat pas user submit file excel berisi list PIB
		$('#formExcelUpload').submit(function(e) {
			e.preventDefault();
			// save form object
			var frm = this;
			// generate form data
			var frmData = new FormData(this);
			// frmData.append('MAX_FILE_SIZE', '32');
			// must append files manually :(
			$.each( $(this).find('input[type="file"]'), function(k, v) {
				// console.log(v);

				frmData.append(v.name, v.files);
			} );			

			// console.log(this);

			// $('#blocker').show();
			showBlocker('Parsing data...might take a while...');
			
			$.ajax({
				url: frm.action,
				type: 'POST',
				data: frmData,
				cache: false,
				processData: false,
				contentType: false,
				success: function(data, status, jqXHR) {
					if (typeof data.error === 'undefined') {
						// console.log("Success");
						console.log(data);	

						// it's a JSON already, yaay
						for (var i=0; i<data.length; i++) {
							// console.log(data[i].no_pib + " :: " + data[i].tgl_pib);

							addNewRow(data[i].no_dok, data[i].tgl_dok, data[i].importir, data[i].jml_item, data[i].berat_kg);
						}

						// update the number manually?
						$('#tblListPIB .sixPad').blur();
					} else {
						alert('ErrorData: ' + data.error);
					}
					// $('#blocker').hide();
					hideBlocker();
				},
				error: function(jqXHR, status, errorObj) {
					alert("Error("+jqXHR.status+"):\n"+jqXHR.statusText);
					console.log(jqXHR);
					// $('#blocker').hide();
					hideBlocker();
				}
			});
		});

		// behavior buat pas submit form 
		// tiap image diekstrak datanya ke input
		$('#formListPIB').submit(function(e) {
			e.preventDefault();
			e.stopPropagation();

			// first, make sure it's not an empty row
			var rowCount = $(this).find('#tblListPIB tbody tr').length;

			if (rowCount < 1) {
				alert("Empty form cannot be sent!");
				return false;
			}
			// first, we extract each imagePreview source
			var canContinue = true;
			// into a hidden input
			$.each($(this).find('input[name="img_src[]"]'), function(k, v) {
				// console.log(v);
				// grab sibling
				var src = $(v).closest('td').find('.imagePreview').attr('src');
				$(v).val(src);

				// kalo kosong, eror
				if ($(v).val().length < 1) {
					canContinue = false;
					return false;
				}
			});

			if (!canContinue) {
				alert("Ada foto kesiapan barang yang kosong. Cek lagi.");
				return false;
			}

			// extract filename to specified input
			$.each($(this).find('input[name="img_name[]"]'), function(k, v) {
				// console.log(v);
				var filename = $(v).closest("td").find('.filename').text();
				$(v).val(filename);
			});

			var fd = new FormData(this);
			var target = this.action;

			console.log(this);

			// tampilin blocker
			showBlocker("Sending request...");

			$.ajax({
				url: target,
				type: 'POST',
				data: fd,
				cache: false,
				processData: false,
				contentType: false,

				success: function(data, status, jqXHR) {
					hideBlocker();

					console.log(data);

					if (!data.success) {
						alert(data.error);
					} else {
						alert(data.msg);
						// redirect
						window.location.href = data.redirect;
					}
				},

				error: function(jqXHR, status, errorObj) {
					hideBlocker();

					console.log(jqXHR);
				}
			});
		});

		
		// buat imageBrowse, tambahin fitur preview
		$('#tblListPIB').on('change', '.imageBrowse', function(e) {
			// console.log("Ah, the file changes!");
			// console.log(e);

			var row = $(this).closest('tr');
			var image = this.files[0];

			setRowImage(row, image);

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

		// buat hapus image yg udh dibrowse
		$('body').on('click', 'input[data-action="imgClear"]', function() {
			$(this).closest('tr').find('.imageBrowse').val('').show();
			$(this).closest('tr').find('.imagePreview').attr('src','').hide();
			$(this).closest('tr').find('.filename').text('');
			// $(this).closest('tr').find('.imgLink').attr('href', '#');
		});
		
	});
</script>