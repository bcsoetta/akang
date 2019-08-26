<!doctype html>
<html>
	<head>
		<title><?php echo $pagetitle;?></title>
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('favicon.ico'); ?>">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<?php
		// ugly hack! to ensure calendar gif is loaded no matter what
		echo "<script>let calendar_gif='".get_img_path('calendar.gif')."'</script>";
		?>
		<?php
		link_css('jquery-ui.min.css');
		link_css('jquery.multiselect.css');
		link_css('reset.css');
		link_css('style.css');

		link_js('jquery.min.js');
		link_js('jquery-ui.min.js');
		link_js('jquery.multiselect.js');
		link_js('menu.js');
		link_js('controls.js');
		?>

		<link rel="stylesheet" media="screen and (min-width: 961px) and (max-width: 1024px)" type="text/css" href="<?php echo base_url('assets/css/1024px.css');?>">
		<link rel="stylesheet" media="screen and (min-width: 601px) and (max-width: 960px)" type="text/css" href="<?php echo base_url('assets/css/960px.css');?>">

		<script type="text/javascript">
		$(function(){
			$('#umsg').fadeToggle('slow');
			setTimeout(function(){
				$('#umsg').fadeToggle('slow');
			}, 5000);
		});
		</script>
	</head>
	<body>
		<div id="header">
			<div class="container clearfix" id="headerContainer">
				<img src="<?php link_img('logo.png');?>" height="64"/>
				<span id="appTitle">Aplikasi Kesiapan Barang <?php echo date('Y');?></span>
				<span id="logout">Selamat bekerja, <?php echo $user['fullname']; ?> <a href="<?php echo base_url('user/logout');?>">(Logout)</a></span>
			</div>
		</div>
		<nav>
			<div class="container clearfix">
				
				<?php
				if(isset($menu))
					echo $menu;
				?>
			</div>
		</nav>
		<div class="container" id="mainContent">
			<?php
			if(isset($mainContent))
				echo $mainContent;
			?>
		</div>

		<?php
		//handle user message. set using $user->message('stuff');
		if(isset($message) && strlen($message)){
		?>
		<p id="umsg">
		<?php echo $message;?>
		</p>
		<?php
		}
		?>
	</body>
</html>