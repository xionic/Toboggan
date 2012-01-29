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
				mp3: "",
				});
			},
			swfPath: "./js/jQuery.jPlayer.2.1.0/",
			supplied: "mp3",
			wmode: "window"
		}).bind($.jPlayer.event.ended, function(event){
			//$("#playlistTracks")
			
			//get the currently played one
			var playingObject = $("#playlistTracks .jPlaying");
			playingObject.removeClass("jPlaying");
			
			var nextObjectSpan = playingObject.parent().next("li").children("a");
			if(!nextObjectSpan)
				return;
			
			var streamSource = ""+$(nextObjectSpan).attr("data-dir")+""+$(nextObjectSpan).attr("data-filename");
			$(nextObjectSpan).addClass("jPlaying");
		                        
			console.debug("streamFile.php?file="+streamSource);
			
			$("#jquery_jplayer_1").jPlayer( "setMedia", {
					"mp3" : "streamFile.php?file="+streamSource
				}).jPlayer("play");
			
			nextObjectLi.children("span");
			
		});
	
		updateFolderBrowser();
	});

	/**
		Adds the handlers to the track list in the "album view"
	*/
	function addTrackClickHandlers()
	{
		$("#tracklist").children("li").children("a").click(function(){
			
			var trackTagObject = $(this).parent().children("span.trackName");
			
			$("<li></li>").append(
				$("<a href='javascript:;'></a>")
					.text( trackTagObject.text() )
					.attr( "data-filename", trackTagObject.attr("data-filename"))
					.attr( "data-dir", trackTagObject.attr("data-dir"))
			).appendTo($("#playlistTracks"));
			
			addPlaylistClickHandlers();
		});
		$( "#tracklist li" ).draggable({
			appendTo: "body",
			helper: "clone"
		});
		/** Make the playlist drag-sortable & Droppable*/		
		$("#playlistTracks").droppable({
			accept: ":not(.ui-sortable-helper)",	//make sure that if its being rearranged this doesn't count as a drop
			drop: function( event, ui ) {
				$( this ).find( ".placeholder" ).remove();
				
				//TODO: Extract the metadata information for the track and clone it as well as the filename etc
				//or some sort of deep clone:
				//$(ui.draggable).clone().appendTo(this);
				var trackTagObject = $(ui.draggable).children("span.trackName");
				$("<li></li>").append(
					$("<a href='javascript:;'></a>")
						.text( trackTagObject.text() )
						.attr( "data-filename", trackTagObject.attr("data-filename"))
						.attr( "data-dir", trackTagObject.attr("data-dir"))
				).appendTo(this);
				
				addPlaylistClickHandlers();
			}
		}).sortable({
			items: "li:not(.placeholder)",
			sort: function() {
				// gets added unintentionally by droppable interacting with sortable
				// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
				$( this ).removeClass( "ui-state-default" );
			}
		});
		
		addPlaylistClickHandlers();
		
	}
	
	/**
		Click Handler for folders in the folder browser
	*/
	function addFolderClickHandlers()
	{
		$("#folderlist").children("li").children("a").click(function(){
			updateFolderBrowser(this);
		});
	}
	
	/**
		Click Handler for tracks in the playlist
	*/
	function addPlaylistClickHandlers()
	{
		$("#playlistTracks li a").unbind("click");
		$("#playlistTracks li a").click(function(){
			var streamSource = ""+$(this).attr("data-dir")+""+$(this).attr("data-filename");
			
			$("#playlistTracks .jPlaying").removeClass("jPlaying");
			
			$(this).addClass("jPlaying");
	
			console.debug("streamFile.php?file="+streamSource);
			
			$("#jquery_jplayer_1").jPlayer( "setMedia", {
				"mp3" : "streamFile.php?file="+streamSource
			}).jPlayer("play");
		});
	}
	
	/**
		Actually updates the folder browser with content
	*/
	function updateFolderBrowser(clickedObj)
	{
		var folderName = "";
		
		if(clickedObj)
			folderName = $(clickedObj).attr("data-parent")+""+$(clickedObj).text();
			
		//retrieve a list of new folders
		$.ajax({
			cache: false,
			url: "list_files.php",
			type: "GET",
			data: { 'dir' : folderName },
			complete: function(jqxhr, status) {},
			error: function(jqxhr, status, errorThrown) {
				alert("AJAX ERROR - check the console!");
				console.error(jqxhr, status, errorThrown);
			},
			success: function(data, status, jqxhr) {
				
				$("#folderlist").empty();
				
				for (dir in data.Directories)
				{
					$("<li></li>").append(
						$("<a href='javascript:;'></a>")
							.text(data.Directories[dir])
							.attr("data-parent",data.CurrentPath)
					)
					.appendTo($("#folderlist"));
				}
				addFolderClickHandlers();
				
				//TODO: List files too
				//data.Files
				$("#tracklist").empty();
				for (file in data.Files)
				{	//<li><a href='javascript:;'>+</a> <span class='trackName' data-trackpath='testMP3_1.mp3'>Album Track One</span></li>
					$("<li></li>").append(
						$("<a href='javascript:;'>+</a> ")
					).append(
						$("<span class='trackName'></span>")
							.text(data.Files[file])
							.attr("data-dir",folderName+"/")
							.attr("data-filename",data.Files[file])					
					).appendTo($("#tracklist"));
				}
				addTrackClickHandlers();
				
			},
		});
	}
	
})();
