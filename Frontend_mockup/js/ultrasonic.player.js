$(document).ready(function(){
	$("#jquery_jplayer_1").jPlayer({
		ready: function () {
			$(this).jPlayer("setMedia", {
			mp3: "testMP3.mp3",
			});
		},
		swfPath: "./js/jQuery.jPlayer.2.1.0/",
		supplied: "mp3",
		wmode: "window"
	});
});