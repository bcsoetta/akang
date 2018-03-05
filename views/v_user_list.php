<?php
if (!isset($userlist))
	$userlist = array();
?>

<div>
	<div id="divider">
		<a href="<?php echo base_url('user/add');?>" class="commonButton shAnim blueGrad inblock" id="btnAddUser">Tambah User</a>
	</div>
	<hr>
	<h2>
		List User terdaftar
	</h2>
	<table class="table" id="tblUser">
		<thead>
			<th>No</th>
			<th>Username</th>
			<th>Fullname</th>
			<th>Role</th>
			<th>Gudang (PPJK/PJT Only)</th>
			<th>Aktif</th>
			<th></th>
		</thead>
		<tbody>

			<?php
			$nomor = 1;
			foreach ($userlist as $user) {
							
			?>

			<tr>
				<td> <?php echo $nomor++;?> </td>
				<td> <?php echo $user['username'];?> </td>
				<td> <?php echo $user['fullname'];?> </td>
				<td> <?php echo $user['role'];?> </td>
				<td> <?php echo $user['gudang'];?> </td>
				<td> <?php echo $user['active']; ?> </td>
				<td>
					<a href="<?php echo base_url('app/manage/user/' . $user['id']);?>" class="commonButton inblock shAnim greenGrad">Edit</a>
					<a href="<?php echo base_url('user/delete/' . $user['id']);?>" class="commonButton inblock shAnim redGrad" onclick="return confirm('Delete user : <?php echo $user['fullname']?>?')">Delete</a>
				</td>
			</tr>

			<?php
			}
			?>

		</tbody>
	</table>
	<hr>
</div>

<script type="text/javascript">

$(function() {
	var initTop = $('#btnAddUser').offset().top;

	$(window).scroll(function() {
		var currHeight = $(window).scrollTop();

		console.log('ch: ' + currHeight + ' <> ' + initTop);

		if (currHeight > initTop) {
			$('#divider').addClass('sticky');
		} else {
			$('#divider').removeClass('sticky');
		}
	});
});

</script>