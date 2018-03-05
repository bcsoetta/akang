<?php
// set default data
if (!isset($userdata)) {
	// make default
	$userdata = array(
		'username' => '',
		'fullname' => '',
		'role' => array(),
		'gudang' => array(),
		'active' => 'Y'
	);
}

// set password standar
if (!isset($password)) {
	$password = '';
}

// default mode
if (!isset($mode)) {
	$mode = 'edit';
}

// target url
switch ($mode) {
	case 'add':
		$formAction = base_url('user/register');
		break;

	case 'edit':
		$formAction = base_url('user/simpan');
		break;
	
	default:
		$formAction = base_url('user/simpan');
		break;
}
?>

<form method="POST" action="<?php echo $formAction;?>">
	<?php
	if (isset($userdata['id'])) {
	?>
	
	<input type="hidden" name="id" value="<?php echo $userdata['id'];?>">
	
	<?php
	}
	?>

	<p>
		<span class="si2">Username</span>
		<label>
			<input type="checkbox" name="active" <?php if($userdata['active'] == 'Y')echo "checked";?> >Aktif
		</label>
	</p>
	<p>
		<input type="text" name="username" value="<?php echo $userdata['username'];?>" class="shAnim si" />
		<input type="password" name="password" value="<?php echo $password;?>" class="shAnim si" />
	</p>

	<p>
		<span class="si2">Fullname</span>
	</p>
	<p>
		<input type="text" name="fullname" value="<?php echo $userdata['fullname'];?>" class="shAnim si" />
	</p>
	<hr>
	<p>
		<span class="si2">Gudang (PPJK/PJT Only)</span>
	</p>
	<p>
		<select id="gudang" name="gudang[]" multiple>

			<?php
			foreach ($listGudang as $gudang) {
				
			?>
			
			<option value="<?php echo $gudang;?>" <?php if(in_array($gudang, $userdata['gudang']))echo "selected"; ?> > <?php echo $gudang;?> </option>
			
			<?php

			}
			?>
		</select>
	</p>
	<hr>
	<p>
		<span class="si2">Role</span>
		<ul class="formCheckOptions">
			<li> <label class="pointable"> <input type="checkbox" name="role[]" value="PPJK" <?php echo (in_array('PPJK', $userdata['role']) ? "checked" : "" ) ;?> > PPJK </label> </li>
			<li> <label class="pointable"> <input type="checkbox" name="role[]" value="PJT" <?php echo (in_array('PJT', $userdata['role']) ? "checked" : "" ) ;?> > PJT </label> </li>
			<li> <label class="pointable"> <input type="checkbox" name="role[]" value="ADMIN_PABEAN" <?php echo (in_array('ADMIN_PABEAN', $userdata['role']) ? "checked" : "" ) ;?> > ADMIN PABEAN </label> </li>
			<li> <label class="pointable"> <input type="checkbox" name="role[]" value="PEMERIKSA" <?php echo (in_array('PEMERIKSA', $userdata['role']) ? "checked" : "" ) ;?> > PEMERIKSA </label> </li>
			<li> <label class="pointable"> <input type="checkbox" name="role[]" value="SUPERUSER" <?php echo (in_array('SUPERUSER', $userdata['role']) ? "checked" : "" ) ;?> > SUPERUSER </label> </li>
		</ul>
	</p>
	
	<hr>
	<p>
		
		
		<?php 
		if ($mode == 'edit') {
		?>
		
		<input type="submit" value="Simpan" name="action" class="commonButton blueGrad shAnim" />
		<input type="submit" value="Reset Password" name="action" class="commonButton redGrad shAnim" onclick="return confirm('Reset Password?')"/>
		
		<?php 
		} else if ($mode == 'add') {
		?>

		<input type="submit" value="Tambah" name="action" class="commonButton blueGrad shAnim" />

		<?php
		}
		?>
	</p>
</form>

<script type="text/javascript">
$(function() {
	$('select#gudang').multiselect({
		columns : 4,
		search : true,
		selectAll : true,
		texts : {
			placeholder: 'Pilih gudang',
			search : 'Ketik gudang yang dicari'
		}
	});
});
</script>