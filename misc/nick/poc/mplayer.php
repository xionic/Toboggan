<?php
	require_once("include/functions.php");
	$file = $_GET["file"];
	//get streamers for file
	$streamers = getAvailableStreamers($file);
	if(!$streamers)
		die("File type not supported");
							
?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="jPlayer/blue.monday/jplayer.blue.monday.css" />
	<script type="text/javascript" src="jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="jPlayer/jquery.jplayer.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#jquery_jplayer_1").jPlayer({
				ready: function(){
					$(this).jPlayer("setMedia", {
						<?			
						//string for supplied parameter later
						$suppliedStr="";
						foreach($streamers as $s){
							$suppliedStr.= $s->toExt.",";
							echo $s->toExt.": 'https://ssl.xionic.co.uk/projects/ultrasonic/misc/nick/poc/getStream.php?file=".urlencode($file)."&profile=".$s->id."',\n";
							//echo "flv: 'https://ssl.xionic.co.uk/projects/ultrasonic/misc/nick/testmedia/testflvs/ad.flv'";
						}
							
						?>
					});
				},
				swfPath: "./jPlayer/",
				supplied: "<?php echo $suppliedStr; ?>",
				//wmode: "window",
				size: {
					width: "640px",
					height: "360px",
					cssClass: "jp-video-360p"
				},
				errorAlerts: true,
			});
		});
	</script>
</head>
<body>
	<div id="jquery_jplayer_1" class="jp-jplayer"></div>

	<div id="jp_container_1" class="jp-video jp-video-360p">
		<div class="jp-type-single">
			<div id="jquery_jplayer_1" class="jp-jplayer"></div>
			<div class="jp-gui">
				
				<div class="jp-interface">
					<div class="jp-progress">
						<div class="jp-seek-bar">
							<div class="jp-play-bar"></div>
						</div>
					</div>
					<div class="jp-current-time"></div>
					<div class="jp-duration"></div>
					<div class="jp-title">
						<ul>
							<li>Big Buck Bunny Trailer</li>
						</ul>
					</div>
					<div class="jp-controls-holder">
						<ul class="jp-controls">
							<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
							<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
							<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
							<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
							<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
							<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
						</ul>
						<div class="jp-volume-bar">
							<div class="jp-volume-bar-value"></div>
						</div>

						<ul class="jp-toggles">
							<li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>
							<li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>
							<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
							<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="jp-no-solution">
				<span>Update Required</span>
				To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
			</div>
		</div>
	</div>

	
</body>
</html>