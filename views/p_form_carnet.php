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
		$('#frmCarnet').submit(function(){
			$('form select').removeAttr('disabled');
		});

		//button handler
		$('body').on('click', '.viewBtn', function(){
			var src=$(this).closest('.imgframe').find('img').attr('src');
			$('#previewImg').attr('src', src);
			$('#preview').fadeToggle('slow');
		});

		$('body').on('click', '.delBtn', function(){
			var me =this;
			var id =$(this).closest('.imgframe').data('id');
			var prn =$(this).closest('.imgframe');

			$(prn).addClass('busy');

			$.ajax({
				url: 'http://192.168.146.250/carnet/app/delete_image',
				data: {'id': id},
				type: 'POST',
				error: function(a,b,c){
					alert('Error deleting image!!');
					$(prn).removeClass('busy');
				},
				success: function(a,b,c){
					alert('gambar berhasil dihapus!');
					$(prn).removeClass('busy');
					$(prn).remove();
				}
			});
		});

		//set drop handler
		$.each($('.dropbox'), function(id, elem){
			//init part
			var accept_upload=true;
			$('#progressbar').width(0);
			var url=$(elem).data('url');

			function begin_upload(){
				accept_upload=false;
				$('#progressbar').width(0);
				$('.dropbox').removeClass('dropping');
				$('.dropbox').addClass('disabled');
			}

			function end_upload(){
				setTimeout(function(){
					accept_upload=true;
					$('#progressbar').width(0);
					$('.dropbox').removeClass('disabled');
				}, 1000);
			}

			function onprogress(e){
				if(e.lengthComputable){
					var percent = Math.round(e.loaded/e.total*100);
					console.log('upload: '+percent+'%');
					$('#progressbar').width(percent+'%');
				}
			}

			elem.addEventListener('drop', function(ev){
				ev.preventDefault();
				ev.stopPropagation();
				console.log('drop');
				if(!accept_upload)
					return;	//do nothing
				//prepare data
				begin_upload();
				var files=ev.dataTransfer.files;
				var formdata=new FormData();
				formdata.append('MAX_FILE_SIZE', 8192192);	//MAX 8 MB TOTAL
				for(var i=0; i<files.length; i++){
					formdata.append('file[]', files[i]);
				}
				//ajax upload
				$.ajax({
					url: "http://192.168.146.250/carnet/app/handle_upload",
					data: formdata,
					type: 'POST',
					crossDomain:true,
					xhr: function(){
						var myXHR = $.ajaxSettings.xhr();
						if(myXHR.upload)
							myXHR.upload.addEventListener('progress', onprogress, false);
						return myXHR;
					},
					success: function(a,b,c){
						console.log('success: '+a+':'+b+':'+c);
						end_upload();

						console.log(a);

						var data = JSON.parse(a);
						for(var i=0; i<data.file.length; i++){
							add_img_thumb(data.file[i]);
						}

						var msg='';
						for(var i=0; i<data.msg.length; i++)
							msg+=data.msg[i]+"\n";
						if(msg.toString().length>3)alert(msg);
					},
					error: function(a,b,c){
						console.log(url);
						console.log('error: '+a+':'+b+':'+c);
						alert('AJAX error. '+a+' : '+b+' : '+c);
						console.log(a);
						console.log(b);
						console.log(c);
						end_upload();
					},
					processData: false,
					cache: false,
					contentType: false
				});
			});

			elem.addEventListener('dragover', function(ev){
				ev.preventDefault();
				ev.stopPropagation();
				console.log('dragover');
				if(accept_upload)
					$(elem).addClass('dropping');
			});

			elem.addEventListener('dragleave', function(ev){
				ev.preventDefault();
				ev.stopPropagation();
				console.log('dragleave');
				$(elem).removeClass('dropping');
			});
		});

		//init page
		set_switch_val($('#jenis_switch').val());

		//ambil upload cache
		$.ajax({
			type: 'GET',
			url: '<?php echo base_url('app/get_upload_cache');?>',
			success: function(a,b,c){
				var data = JSON.parse(a);
				console.log(a);
				//add thumb for each shit
				for(var i=0; i<data.length; i++){
					//alert(data[i].filename+' = '+data[i].id+' = '+data[i].downlink+ ' : '+data[i].name);
					add_img_thumb(data[i]);
				}
			},
			error: function(a,b,c){
				alert('error getting upload cache!');
			}
		});
	});
	</script>
	<form id="frmCarnet" METHOD="POST" action="<?php echo base_url('app/db_add_carnet');?>">
		<div class="tableL">
			<div class="cellL">
				<?php
				if(isset($mode)){
				?>
				<p>No. Agenda</p>
				<input type="text" value="00" disabled/>
				<?php
				}
				?>
				<p>Jenis Carnet</p>
				<select name="carnettype" class="styled shAnim">
					<option value="ATA">ATA-Carnet</option>
					<option value="CPD">CPD-Carnet</option>
				</select>
				<p>No Carnet</p>
				<input type="text" name="carnetno" class="si shAnim"/>
				<p>Tgl Carnet</p>
				<input type="text" name="carnetdate" class="datepicker shAnim"/>
				<p>Nama Holder</p>
				<input type="text" name="carnetholder" class="si shAnim"/>				
			</div>
			<div class="cellL">
				<p>Berlaku Sampai</p>
				<input type="text" name="carnetexpire" class="datepicker shAnim"/>
				<p>Lokasi Pengajuan</p>
				<select name="carnetloc" class="styled shAnim si">
					<?php if($user['kodeLokasi'] & LOK_TERM){ ?><option value="TERM" selected>Terminal - (TERM)</option><?php } ?>
					<?php if($user['kodeLokasi'] & LOK_CARGO){ ?><option value="CARGO" selected>Gudang RH - (CARGO)</option><?php } ?>
				</select>
				<p>Jenis Pengajuan</p>
				<select name="jenis_peng" class="styled shAnim" id="jenis_switch">
					<option VALUE="ERI" selected="selected">Ekspor - Reimpor</option>
					<option VALUE="IRE">Impor - Reekspor</option>
				</select>
				<p>Tanggal Rencana <span class="jenis_value">DUMMEHHH!!</span></p>
				<input type="text" name="ren_tgl_tutup" class="datepicker shAnim"/>
				<p>Rencana Lokasi <span class="jenis_value"></span></p>
				<textarea name="ren_lok_tutup" class="sta shAnim"></textarea>
			</div>
			<div class="cellL">
				<?php 
				if(!isset($pagemode)){
				?>
				<p>Drop hasil scan dokumen di sini (max 2MB/file)</p>
				<div class="dropbox" data-url="http://s2.soetta.com/carnet/app/handle_upload">
					<p class="droptext">Drop Hasil Scan Di Sini</p>
				</div>
				<div class="progressbar">
					<div id="progressbar" class="lnAnim">
					</div>
				</div>
				<?php
				}
				?>
			</div>
		</div>
		<div class="elmbottom">
			<p>Hasil Scan Dokumen</p>
			<div id="imgscan" class="clearfix">

			</div>
		</div>
		<div class="elmbottom">
			<input type="submit" value="Rekam" class="commonButton greenGrad shAnim"/>
			<input type="reset" value="Reset" class="commonButton redGrad shAnim"/>
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
