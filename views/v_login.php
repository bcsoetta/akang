<!doctype html>
<html>
	<head>
		<title><?php echo $pagetitle;?></title>
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('favicon.ico'); ?>">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<?php
		link_css('jquery-ui.min.css');
		link_css('reset.css');
		link_css('style.css');

		link_js('jquery.min.js');
		link_js('jquery-ui.min.js');
		?>

		<link rel="stylesheet" media="screen and (min-width: 961px) and (max-width: 1024px)" type="text/css" href="<?php echo base_url('assets/css/1024px.css');?>">
		<link rel="stylesheet" media="screen and (min-width: 601px) and (max-width: 960px)" type="text/css" href="<?php echo base_url('assets/css/960px.css');?>">

		<script type="text/javascript">
		$(function(){
			$('#username').focus();
		})
		</script>
	</head>
	<body>
		<div id="loginContainer">
			<div class="dialogTitle">
				<p>Aplikasi Kesiapan Barang</p>
			</div>
			<div class="textual section">
				<p style="text-align:center;">Login</p>
			</div>
			<form id="loginForm" class="clearfix" method="POST" action="<?php echo base_url('user/validatesso');?>">
				<div class="textual section">
					<p>
						<span>Username</span>
						<input type="text" name="username" id="username" class="shAnim"/>
					</p>
					<p>
						<span>Password</span>
						<input type="password" name="password" class="shAnim"/>
						<?php
						if(isset($_GET['error'])){
							
								
								?>

								<p class="errorText">
									<?php
									echo $_GET['error'];
									?>
								</p>

								<?php						
						}
						?>
					</p>
				</div>
				<div class="submit">
					<input type="submit" value = "Login" class="commonButton shAnim"/>
				</div>
			</form>
		</div>
	</body>
</html>