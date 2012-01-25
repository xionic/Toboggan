/**
	Holds the JS used for the player system, playlist management etc
*/
(function(){
	/**
		jQuery Entry Point
	*/
	$(document).ready(function(){
		/** Setup the jPlayer */
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
		
		/** Make the playlist drag-sortable */
		$( "#playlistTracks" ).sortable({
			placeholder: "ui-state-highlight"
		});
		$( "#playlistTracks" ).disableSelection();
		
		addTrackClickHandlers();
		addFolderClickHandlers();
		
	});

	/**
		Adds the handlers to the track list in the "album view"
	*/
	function addTrackClickHandlers()
	{
		$("#tracklist").children("li").children("a").click(function(){
			
			var obj = $("<a href='javascript:;'></a>");
			obj.text($(this).parent().children("span.trackName").text());
			
			$("#playlistTracks").append(
				$("<li/>").append(obj)
			);
		});
	}
	
	/**
		Click Handler for folders in the folder browser
	*/
	function addFolderClickHandlers()
	{
		$("#folderlist").children("li").children("a").click(function(){
			updateFolderBrowser();
		});
	}
	
	/**
		Actually updates the folder browser with content
	*/
	function updateFolderBrowser()
	{
		//retrieve a list of new folders
		
		//update the content holders
				
		addFolderClickHandlers();
	}
	
})();