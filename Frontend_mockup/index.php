<!DOCTYPE html>
<html>
<head>
	<link href='./css/layout.css' rel='stylesheet' type='text/css' />
	<link href='./css/theme.css' rel='stylesheet' type='text/css' />
	<!-- jPlayer theme -->
	<link href='./css/jQuery.jPlayer.Theme/jplayer.ultrasonic.css' rel='stylesheet' type='text/css' />
	
	<script type="text/javascript" src="./js/jQuery/jQuery.1.7.1.min.js"></script>
	<script type="text/javascript" src="./js/jQuery.jPlayer.2.1.0/jquery.jplayer.min.js"></script>
	<script type="text/javascript" src="./js/ultrasonic.player.js"></script>
	<title>"Ultrasonic" Mockup</title>
</head>
<body>

<div id='playlistContainer'>
	<div class='containerPadding'>
		<?php include 'playlist.include.php'; ?>
	</div>
</div>

<div id='folderbrowserContainer'>
	<div class='containerPadding'>
		<?php include 'folderbrowser.include.php'; ?>
	</div>
</div>

<div id='trackbrowserContainer'>
	<div class='containerPadding'>
		<?php include 'trackbrowser.include.php'; ?>
	</div>
</div>

<div id='playerContainer'>
	<div class='containerPadding'>
		<?php
			include 'player.include.php';
		?>
	</div>
</div>

</body>
</html>