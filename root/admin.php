<!DOCTYPE html>
<html>
<head>

	<!-- jPlayer theme -->
	<link href='./css/jQuery.jPlayer.Theme/jplayer.Toboggan.css' rel='stylesheet' type='text/css' />
	<link href='./css/jQuery-ui/custom-theme/jquery-ui-1.8.21.custom.css' rel='stylesheet' type='text/css' />
	<link href='./css/jQuery.dynatree/default.css' rel='stylesheet' type='text/css' />

	<link href='./css/?spinner.css' rel='stylesheet' type='text/css' />
	
	<!-- internal stylesheets -->
	<link href='./css/?layout.css' rel='stylesheet' type='text/css' />
	<link href='./css/?theme.css' rel='stylesheet' type='text/css' />
	<link href='./css/?admin.css' rel='stylesheet' type='text/css' />

	<script type="text/javascript" src="./js/jQuery/jQuery.1.7.1.min.js"></script>
	<script type="text/javascript" src="./js/jQuery-ui/jquery-ui-1.8.21.custom.min.js"></script>
	<script type="text/javascript" src="./js/jQuery.dynatree/jquery.dynatree.min.js"></script>

	<script type="text/javascript">
<?php
	//this is a hack really, there must be a better way
	echo "var g_Toboggan_basePath=\"".dirname($_SERVER['REQUEST_URI'])."/\";";
?>
	</script>
	<script type="text/javascript" src="./js/Toboggan.admin.js"></script>

	<!-- login form related -->
	<script type="text/javascript" src="./js/sha.js"></script>

	<title>Toboggan</title>
</head>
<body>
<div id='loginFormContainer'>
	<?php include 'loginForm.include.php'; ?>
</div>

</body>
</html>
