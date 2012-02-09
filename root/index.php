<!DOCTYPE html>
<html>
<head>

	<!-- jPlayer theme -->
	<link href='./css/jQuery.jPlayer.Theme/jplayer.ultrasonic.css' rel='stylesheet' type='text/css' />
	<link href='./css/jQuery-ui/smoothness/jquery-ui-1.8.17.custom.css' rel='stylesheet' type='text/css' />

	<!-- internal stylesheets -->
	<link href='./css/?layout.css' rel='stylesheet' type='text/css' />
	<link href='./css/?theme.css' rel='stylesheet' type='text/css' />
	
	<script type="text/javascript" src="./js/jQuery/jQuery.1.7.1.min.js"></script>
	<script type="text/javascript" src="./js/jQuery-ui.1.8.17/jquery-ui-1.8.17.custom.min.js"></script>
	<script type="text/javascript" src="./js/jQuery.jPlayer.2.1.0/jquery.jplayer.min.js"></script>
	<script type="text/javascript" src="./js/jQuery.jPlayer.2.1.0/add-on/jquery.jplayer.inspector.js"></script>
	<script type="text/javascript">
<?php
	//TODO: make this handle if the URI was accessed via index.php
	echo "var g_ultrasonic_basePath=\"{$_SERVER['REQUEST_URI']}\";";
?>
	</script>
	<script type="text/javascript" src="./js/ultrasonic.player.js"></script>

	<!-- login form related -->
	<script type="text/javascript" src="./js/sha.js"></script>

	<title>"Ultrasonic" Mockup</title>
</head>
<body>

<div id='topBarContainer'>
	<?php include 'topbar.include.php'; ?>
</div>

<div id='playlistContainer'>
	<?php include 'playlist.include.php'; ?>
</div>

<div id='folderbrowserContainer'>
	<select id='mediaSourceSelector'>
		<option class='placeholder'> -- Select one -- </option>
	</select>
	<?php include 'folderbrowser.include.php'; ?>
</div>

<div id='centreContainer'>
	<?php include 'trackbrowser.include.php'; ?>
</div>

<div id='playerControlsContainer'>
	<?php include 'playerControls.include.php';	?>
</div>

<div id='loginFormContainer'>
	<?php include 'loginForm.include.php'; ?>
</div>

<div id='jPlayerInspector'></div>

</body>
</html>
