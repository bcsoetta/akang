<?php
//nilai standar dari data carnet
if(!isset($carnet))
	$carnet = array(
		'id'=>0,
		'agenda'=>'',
		'tgl_carnet'=>'',
		'tgl_kadaluarsa'=>'',
		'lokasi'=>'CARGO',
		'cnt_type'=>'ATA',
		'ren_lok_tutup'=>'',
		'tgl_tutup'=>'',
		'jenis_peng'=>'IRE'
		);
?>
<div>
	<script type="text/javascript">
	
	$(function(){
		function set_switch_val(a){
			if(a=='ERI')
				$('.jenis_value').text('Re-Impor');
			else
				$('.jenis_value').text('Re-Ekspor');
		}

		function add_img_thumb(d){
			if(!d)
				return;
			//d has 3 elements: id, filename, downlink
			var proto = $('#imgproto');

			$(proto).find('img').attr('src', d.filename);
			$(proto).find('p').text(d.name);
			$(proto).find('input').val(d.id);
			$(proto).find('a').attr('href', d.downlink);

			var html = $(proto).html();
			//alert(html);

			var d = $('<div>', {'data-id': d.id, 'class': 'imgframe'});
			d.append(html);
			//alert($(proto).find('img').attr('src'));
			$('#imgscan').append(d);
		}

		$('#jenis_switch').change(function(e){
			var val=$(this).val();
			set_switch_val($('#jenis_switch').val());
		});

		//set before submit
		$('#frmCarnet').submit(function(e){
			e.preventDefault();
		});

		$('#btnCloseCarnet').click(function(){
			//alert('close!!');
			$('#closeForm').fadeToggle('slow');
		});

		$('.btnFloatClose').click(function(){
			$(this).closest('.floatForm').fadeToggle('slow');
		});

		$('#btnPrintCF').click(function(){
			var me_url=$(this).data('url');
			$.ajax({
				type: 'GET',
				url: me_url,
				success:function(a,b,c){
					console.log(a);
				},
				error:function(a,b,c){
					
				}
			});
		});

		//button handler
		$('body').on('click', '.viewBtn', function(){
			var src=$(this).closest('.imgframe').find('img').attr('src');
			$('#previewImg').attr('src', src);
			$('#preview').fadeToggle('slow');
		});

		//init page
		set_switch_val($('#jenis_switch').val());
	});
	</script>
	<form id="frmCarnet" METHOD="POST">
		<div class="tableL">
			<div>
				<div class="cellL">
					<p>No. Agenda</p>
					<input type="text" value="<?php echo $carnet['agenda'];?>" class="si shAnim" disabled/>
					
					<p>Jenis Carnet</p>
					<select name="carnettype" class="styled shAnim" disabled>
						<option value="ATA" <?php if($carnet['cnt_type'] == 'ATA')echo 'selected';?> >ATA-Carnet</option>
						<option value="CPD" <?php if($carnet['cnt_type'] == 'CPD')echo 'selected';?> >CPD-Carnet</option>
					</select>
					<p>No Carnet</p>
					<input type="text" name="carnetno" class="si shAnim" disabled value="<?php echo $carnet['cnt_no'];?>"/>
					<p>Tgl Carnet</p>
					<input type="text" name="carnetdate" class="datepicker shAnim" disabled value="<?php echo $carnet['tgl_carnet'];?>"/>
					<p>Nama Holder</p>
					<input type="text" name="carnetholder" class="si shAnim" disabled value="<?php echo $carnet['cnt_holder'];?>"/>				
				</div>
				<div class="cellL noRB">
					<p>Berlaku Sampai</p>
					<input type="text" name="carnetexpire" class="datepicker shAnim" disabled value="<?php echo $carnet['tgl_kadaluarsa'];?>"/>
					<p>Lokasi Pengajuan</p>
					<select name="carnetloc" class="styled shAnim si" disabled>
						<option value="TERM" <?php if($carnet['lokasi'] == 'TERM')echo 'selected';?> >Terminal - (TERM)</option>
						<option value="CARGO" <?php if($carnet['lokasi'] == 'CARGO')echo 'selected';?> >Gudang RH - (CARGO)</option>
					</select>
					<p>Jenis Pengajuan</p>
					<select name="jenis_peng" class="styled shAnim" id="jenis_switch" disabled>
						<option VALUE="ERI" <?php if($carnet['jenis_peng'] == 'ERI')echo 'selected';?> >Ekspor - Reimpor</option>
						<option VALUE="IRE" <?php if($carnet['jenis_peng'] == 'IRE')echo 'selected';?> >Impor - Reekspor</option>
					</select>
					<p>Tanggal Rencana <span class="jenis_value">DUMMEHHH!!</span></p>
					<input type="text" name="ren_tgl_tutup" class="datepicker shAnim" disabled value="<?php echo $carnet['tgl_tutup'];?>"/>
					<p>Rencana Lokasi <span class="jenis_value"></span></p>
					<textarea name="ren_lok_tutup" class="sta shAnim" disabled><?php echo $carnet['ren_lok_tutup'];?></textarea>
				</div>
				<div class="elmbottom">
					<button type="button" class="blueGrad shAnim commonButton" id="btnPrintCF" data-id="<?php echo $carnet['id'];?>" data-url="<?php echo base_url('app/cetaklk/'.$carnet['id']);?>">Cetak Lembar Kontrol</button>
					<?php
					if($carnet['status']=='Open'){
					?>
					<button type="button" class="redGrad shAnim commonButton" id="btnCloseCarnet">Tutup Pengajuan</button>
					<?php
					}else{
					?>
					<button type="button" class="disabled shAnim commonButton">Sudah ditutup</button>
					<?php
					}
					?>
				</div>
			</div>
			<div class="cellL clearfix LB">
				<p>Lampiran Scan Dokumen</p>
				<div id="imgscan" class="clearfix">
				<?php
				if(isset($images)){
					foreach($images as $img){
				?>
					<div class="imgframe" data-id="<?php echo $img['id'];?>">
						<p><?php echo $img['orig_name'];?></p>
						<div>
							<img src="<?php echo base_url('assets/img/upload/'.$img['filename']);?>" width="100px">
						</div>
						<div class="control">
							<button type="button" class="commonButton shAnim blueGrad viewBtn">View</button>
							<a href="#" class="commonButton shAnim greenGrad">Download</a>
						</div>
					</div>
				<?php
					}
				}
				?>
				</div>
			</div>
		</div>
	</form>
	<div id="preview">
		<img id="previewImg" src="" alt="Loading...">
	</div>
	<!--prototype-->
	<div id="imgproto" style="display:none;" class="imgframe">
		<p></p>
		<input type="hidden" name="img_id[]" value=""/>
		<div>
			<img src="" width="100px">
		</div>
		<div class="control">
			<button type="button" class="commonButton shAnim redGrad delBtn">Del</button>
			<button type="button" class="commonButton shAnim blueGrad viewBtn">View</button>
			<a href="#" target="_blank" class="commonButton shAnim greenGrad">Download</a>
		</div>
	</div>
</div>
<div class="floatForm" id="closeForm">
	<form action="<?php echo base_url('app/closecarnet');?>" method="POST">
		<p>Catatan Penutupan : </p>
		<input type="hidden" name="id" value="<?php echo $carnet['id'];?>"/>
		<textarea class="sta shAnim" name="cat_ttp" style="width: 100%; box-sizing: border-box;"></textarea>
		<p>
			<button type="submit" class="commonButton redGrad shAnim">Simpan</button>
			<button type="button" class="commonButton blueGrad shAnim btnFloatClose" id="btnCloseForm">Sembunyikan</button>
		</p>
	</form>
</div>