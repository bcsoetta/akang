<script type="text/javascript">
function pad(str, max){
	str = str.toString();
	return str.length < max? pad('0'+str, max) : str;
}
$(function(){
	//fungsi ini ngisi datepicker yang kosong ke tgl hari ini
	var d = new Date();
	var df = pad(d.getDate(),2)+'/'+pad(d.getMonth()+1,2)+'/'+d.getFullYear();
	$('.datepicker').each(function(){
		var v = $(this).val();
		v = v.trim();
		if(v.length <= 1)
			$(this).val(df);
	});
});
</script>

<?php 
//default buat search form
$datestart=$search['datestart'];
$dateend=$search['dateend'];
$paramtype=$search['paramtype'];
$paramvalue=$search['paramvalue'];
$status=$search['status'];
//print_r($search);
?>
<div>
	<div id="toolbox">
		<form method="POST" action="<?php echo base_url('app/browse');?>">
			<span class="field">Periode Tgl Carnet</span>
			<input type="text" name="datestart" id="datestart" class="datepicker shAnim shInput" value="<?php echo $datestart;?>"/>
			<span>s/d</span>
			<input type="text" name="dateend" id="dateend" class="datepicker shAnim shInput" value="<?php echo $dateend;?>"/>
			<select name="paramtype" class="styled shAnim">
				<option value="agenda" <?php if($paramtype=="agenda")echo 'selected';?> >No Agenda</option>
				<option value="holder" <?php if($paramtype=="holder")echo 'selected';?> >Nama Holder</option>
				<option value="carnetno" <?php if($paramtype=="carnetno")echo 'selected';?> >No Carnet</option>
			</select>
			<input type="text" name="paramvalue" class="shAnim si2 tooltip" value="<?php echo $paramvalue;?>" title="Untuk pencarian agenda, cukup masukkan nomor agenda (tanpa nol di depan, contoh: 24 BUKAN 00024!)" />
			<span class="field">Status</span>
			<select name="status" class="styled shAnim">
				<option value="all" <?php if($status=='all')echo 'selected';?> >Semua</option>
				<option value="open" <?php if($status=='open')echo 'selected';?> >Open</option>
				<option value="closed" <?php if($status=='closed')echo 'selected';?> >Closed</option>
			</select>
			<input type="submit" name="submit" value="Cari" class="commonButton greenGrad shAnim"/>
			<input type="reset" value="Kosongkan" class="commonButton redGrad shAnim"/>
		</form>
	</div>
	<table class="table">
		<thead>
			<tr>
				<th><div style="min-height: 38px;">No</div></th>
				<th>No Agenda</th>
				<th>No Carnet</th>
				<th>Tgl Carnet</th>
				<th>Holder</th>
				<th>Jenis Carnet</th>
				<th>Jenis Pengajuan</th>
				<th>Kadaluarsa</th>
				<th>Lokasi</th>
				<th>Status</th>
				<th colspan="2">Aksi</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$i=$rowstart;
		foreach($data as $d){
		?>
			<tr>
				<td><div style="min-height: 38px;"><?php echo $i++.'.'?></div></td>
				<td><a href="<?php echo base_url('app/viewcarnet/'.$d['id']);?>" target="_blank" class="iblock"><?php echo $d['agenda'];?></a></td>
				<td><?php echo $d['cnt_no'];?></td>
				<td><?php echo $d['tgl_carnet'];?></td>
				<td><?php echo $d['cnt_holder'];?></td>
				<td><?php echo $d['cnt_type'];?></td>
				<td><?php echo $d['pengajuan'];?></td>
				<td><?php echo $d['expire_carnet'];?></td>
				<td><?php echo $d['lokasi'];?></td>
				<td><?php echo $d['status'];?></td>
				<td><a href="<?php echo base_url('app/editcarnet/'.$d['id']);?>">Edit</a></td>
				<td><a href="<?php echo base_url('app/deletecarnet/'.$d['id']);?>" onclick="return confirm('hapus carnet:\n<?php echo $d['agenda'];?>')" >Hapus</a></td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
	<?php
	//settting link buat pagination
	$hfirst = base_url('app/browse/?pageid=1');
	$hprev = base_url('app/browse/?pageid='.($paging['pageid']-1));
	$hnext = base_url('app/browse/?pageid='.($paging['pageid']+1));
	$hlast = base_url('app/browse/?pageid='.($paging['total_page']));
	if($paging['pageid']==1){
		$hprev = "#";
		$hfirst= "#";
	}
	if($paging['pageid']==$paging['total_page']){
		$hnext = $hlast = "#";
	}
	?>
	<div id="pagingbox">
		<a href="<?php echo $hfirst;?>" class="commonButton redGrad shAnim <?php if($hfirst=="#")echo 'disabled';?>">&lt;&lt;</a>
		<a href="<?php echo $hprev;?>" class="commonButton redGrad shAnim <?php if($hprev=="#")echo 'disabled';?>">&lt;</a>
		<input id="pageid" type="text" class="shAnim spinInput" value="<?php echo $paging['pageid'];?>"/>
		<span><?php echo "/ $paging[total_page] [$paging[total]]" ?></span>
		<a href="<?php echo $hnext;?>" class="commonButton redGrad shAnim <?php if($hnext=="#")echo 'disabled';?>">&gt;</a>
		<a href="<?php echo $hlast;?>" class="commonButton redGrad shAnim <?php if($hlast=="#")echo 'disabled';?>">&gt;&gt;</a>
	</div>
	<script type="text/javascript">
	$('#pageid').keydown(function(e){
		if(e.keyCode==13){
			e.preventDefault();
			//alert('entered '+$(this).val());
			window.location.href = 'http://s2.soetta.com/carnet/app/browse/?pageid='+$(this).val();
		}
	});
	</script>
</div>