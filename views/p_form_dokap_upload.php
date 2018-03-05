<div>
	<form id="frmUploadDokap">
		<h2>
		Header PIB
		</h2>
		<p>
			<span>No PIB</span>
			<input type="text" name="noPIB" class="si shAnim" />
		</p>
		<p>
			<span>Tgl PIB</span>
			<input type="text" name="tglPIB" class="datepicker shAnim" />
		</p>
		<h2>
			List Dokumen Pelengkap
		</h2>
		<div>
			<ul id="listDokap">
			<!-- here goes the list item -->
			</ul>
		</div>
		
	</form>

	<div id="formControl">
		<button id="btnAddDokap" class="commonButton shAnim greenGrad">Tambah Dokap</button>
	</div>

	<div id="protoItemDokap" style="display:none">
		<li>
			<span>Jenis Dokumen</span>
			<select name="docType[]" class="styled shAnim si selDocType">
				<option value="1">Packing List</option>
				<option value="2">Invoice</option>
				<option value="3">MAWB</option>
				<option value="4">HAWB</option>
				<option value="5">Lainnya</option>
			</select>
			<input type="text" min="3" name="docDesc[]" class="docType si shAnim" placeholder="Jenis dokumen..." style="display:none"/>
			<input type="file" name="dokap[]" />
			<button type="button" class="commonButton shAnim redGrad btnDel">Del</button>
		</li>
	</div>

	<script type="text/javascript">
	$(function() {
		// prototip item dokap
		var itemDokap = $('#protoItemDokap').html();

		// when add dokap is clicked
		$('#btnAddDokap').click(function() {
			$("#listDokap").append(itemDokap);
		});

		// behavior of delete button on dokap list item
		$('body').on('click', '.btnDel', function() {
			$(this).closest('p').remove();
		});

		// behavior of docType change (only show docDesc when it's dokumen Lainnya)
		$('body').on('change', '.selDocType', function() {
			var val = $(this).val();

			if (val == 5) {
				$(this).closest('p').find('input.docType').show();
			} else {
				$(this).closest('p').find('input.docType').hide();
			}
		});
	});
	</script>
</div>