<!DOCTYPE html>
<html>
<head>

	<!-- jPlayer theme -->
	<link href='./css/jQuery.jPlayer.Theme/jplayer.ultrasonic.css' rel='stylesheet' type='text/css' />
	<link href='./css/jQuery-ui/custom-theme/jquery-ui-1.8.21.custom.css' rel='stylesheet' type='text/css' />
	<link href='./css/jQuery.dynatree/default.css' rel='stylesheet' type='text/css' />

	<!-- internal stylesheets -->
	<link href='./css/?layout.css' rel='stylesheet' type='text/css' />
	<link href='./css/?theme.css' rel='stylesheet' type='text/css' />

	<script type="text/javascript" src="./js/jQuery/jQuery.1.7.1.min.js"></script>
	<script type="text/javascript" src="./js/jQuery-ui/jquery-ui-1.8.21.custom.min.js"></script>
	<script type="text/javascript" src="./js/jQuery.jPlayer.2.1.0/jquery.jplayer.min.js"></script>
	<script type="text/javascript" src="./js/jQuery.dynatree/jquery.dynatree.min.js"></script>

	<script type="text/javascript" src="./js/jQuery.jPlayer.2.1.0/add-on/jquery.jplayer.inspector.js"></script>
	<script type="text/javascript">
<?php
	// the ./ makes it work in all relevent situations - hack !
	echo "var g_ultrasonic_basePath=\"".dirname($_SERVER['REQUEST_URI']."./")."/\";";
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

<div class='contextMenu vmenu' id='trackMenu' unselectable='on'> <!-- http://www.webdeveloperjuice.com/2010/02/22/create-simple-jquery-right-click-cross-browser-vertical-menu/ -->
	
	<div class="first_li show_containing_dir"><span>Show Containing Directory</span></div>
	<div class="first_li add_to_playlist hideInPlaylist"><span>Add to Playlist</span></div>
	<div class="first_li del_from_playlist hideInTracklist"><span>Remove From Playlist</span></div>
	<div class="first_li play_now hideInPlaylist"><span>Play Now</span></div>
	<div class="first_li download"><span>Download</span></div>
	<div class="first_li"><span>Downcode to...</span>
		<div class="inner_li" id='trackMenu_downcodestreamers'>
			<span>Placeholder1</span>
		</div>
	</div>
	<div class="first_li metadata"><span>Information</span></div>

</div>
</body>
</html>
