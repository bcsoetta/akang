<?php
if (!isset($seconds))
	$seconds = 4;
if (!isset($target))
	$target = base_url('');
if (!isset($targetName))
	$targetName = 'home';
if (!isset($message))
	$message = '..';
?>

<!DOCTYPE html>
<html>
<head>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('favicon.ico'); ?>">
	<meta charset="utf-8">
	<title><?php echo $pagetitle;?></title>

	<?php
	link_js('jquery.min.js');
	?>
</head>
<body>
	<p>
		<?php echo $message;?>. Mengarahkan ke <a href="<?php echo $target;?>"><?php echo $targetName;?></a> dalam <span id="secs"><?php echo $seconds;?></span> detik...
	</p>
	<script type="text/javascript">
		$(function() {
			var secondsLeft = <?php echo $seconds;?>;

			function tick() {
				if (secondsLeft == 0) {
					window.location.href = '<?php echo $target;?>';
				} else {
					secondsLeft--;
					$('#secs').text(secondsLeft);
					setTimeout(tick, 1000);
				}
				
			}

			tick();
		});

		
	</script>
</body>
</html>