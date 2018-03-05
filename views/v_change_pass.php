<?php
?>

<div>
	<form id="frmChangePass" action="<?php echo base_url('user/changepass');?>" method="POST">
		<h2>
			Ganti Password
		</h2>
		<hr>
		<p>
			<span class="si2">Password lama</span>
			<input type="password" name="oldpass" class="shAnim si" />
		</p>
		<hr>
		<p>
			<span class="si2">Password baru</span>
			<input type="password" name="newpass" class="shAnim si" />
			<span>(Minimal 6 karakter)</span>
		</p>
		<p>
			<span class="si2">Password baru (konfirmasi)</span>
			<input type="password" name="confirmpass" class="shAnim si" />
		</p>
		<hr>
		<div>
			<input type="submit" value="Simpan" class="commonButton shAnim blueGrad" />
		</div>
	</form>
</div>

<script type="text/javascript">
$(function() {
	$('#frmChangePass').submit(function(e) {
		// gotta check if there's error

		var newpass = $('input[name="newpass"]').val();
		var confirmpass = $('input[name="confirmpass"]').val();

		var oldpass = $('input[name="oldpass"]').val();

		if (oldpass.length < 6 || newpass.length < 6 || confirmpass.length < 6) {
			alert("Password kependekan");
			return false;
		}

		if (newpass != confirmpass) {

			alert('Password baru tidak konsisten.');
			return false;
		}
	});
});
</script>