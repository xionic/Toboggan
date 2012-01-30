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
			supplied: "mp3,flv",
			wmode: "window"
		}).bind($.jPlayer.event.ended, function(event){

		//get the currently played one
			var playingObject = $("#playlistTracks .jPlaying");
			playingObject.removeClass("jPlaying");
			
			var nextObjectSpan = playingObject.parent().next("li").children("a");
			if(!nextObjectSpan)
				return;
			
			play_jPlayerTrack(nextObjectSpan);
			
			
		});
		$("#mediaSourceSelector").change(function(event){
			updateFolderBrowser($("#mediaSourceSelector").val());
		});
		
		getMediaSources();
	});

	/**
		Make the jquery player play a track from the passed object
	*/
	function play_jPlayerTrack(trackObject)
	{
		
		var		remote_filename = $(trackObject).attr("data-filename"),
				remote_directory = $(trackObject).attr("data-dir"),
				remote_streamers = $(trackObject).attr("data-streamers"),
				remote_mediaSource = $(trackObject).attr("data-media_source");
		
		$("#playlistTracks .jPlaying").removeClass("jPlaying");
		
		$(trackObject).addClass("jPlaying");
		
		//console.debug(remote_filename,remote_directory,remote_mediaSource,remote_streamers);
		
		var streamerObject = $.parseJSON(remote_streamers), mediaObject = {};
		for(var x=0; x<streamerObject.length; ++x)
		{
			mediaObject[streamerObject[x].extension] = g_ultrasonic_basePath+"/backend/rest.php"+"?action=getStream"+
														"&filename="+encodeURIComponent(remote_filename)+
														"&dir="+encodeURIComponent(remote_directory)+
														"&mediaSourceID="+encodeURIComponent(remote_mediaSource)+
														"&streamerID="+streamerObject[x].streamerID;
		}

		$("#jquery_jplayer_1").jPlayer( "setMedia", mediaObject).jPlayer("play");
	}
	
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
					.attr( "data-streamers", trackTagObject.attr("data-streamers"))
					.attr( "data-media_source", trackTagObject.attr("data-media_source"))
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
						.attr( "data-streamers", trackTagObject.attr("data-streamers"))
						.attr( "data-media_source", trackTagObject.attr("data-media_source"))
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
			updateFolderBrowser($(this).attr("data-media_source"),this);
		});
	}
	
	/**
		Click Handler for tracks in the playlist
	*/
	function addPlaylistClickHandlers()
	{
		$("#playlistTracks li a").unbind("click");
		$("#playlistTracks li a").click(function(){
			play_jPlayerTrack(this);
		});
	}
	
	/**
		Actually updates the folder browser with content
	*/
	function updateFolderBrowser(mediaSourceID, clickedObj)
	{
		var folderName = "";
		
		if(clickedObj)
			folderName = $(clickedObj).attr("data-parent")+""+$(clickedObj).text();
		
		//retrieve a list of new folders
		$.ajax({
			cache: false,
			url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listDirContents",
			type: "GET",
			data: { 
				'dir' : folderName, 
				'mediaSourceID' : mediaSourceID
			},
			complete: function(jqxhr, status) {},
			error: function(jqxhr, status, errorThrown) {
				alert("AJAX ERROR - check the console!");
				console.error(jqxhr, status, errorThrown);
			},
			success: function(data, status, jqxhr) {
				
				$("#tracklist").empty();
				$("#folderlist").empty();
				
				for (dir in data.Directories)
				{
					$("<li></li>").append(
						$("<a href='javascript:;'></a>")
							.text(data.Directories[dir])
							.attr("data-parent", data.CurrentPath)
							.attr("data-media_source", mediaSourceID)
					)
					.appendTo($("#folderlist"));
				}
				addFolderClickHandlers();

				//data.Files
				for (file in data.Files)
				{	//<li><a href='javascript:;'>+</a> <span class='trackName' data-trackpath='testMP3_1.mp3'>Album Track One</span></li>
					$("<li></li>").append(
						$("<a href='javascript:;'>+</a> ")
					).append(
						$("<span class='trackName'></span>")
							.text(data.Files[file].displayName)
							.attr("data-dir", folderName+"/")
							.attr("data-filename", data.Files[file].filename)					
							.attr("data-streamers", JSON.stringify(data.Files[file].streamers))
							.attr("data-media_source", mediaSourceID)
					).appendTo($("#tracklist"));
				}
				addTrackClickHandlers();
				
			},
		});
	}
	
	/**
		Get the list of media Sources from the backend
	*/
	
	function getMediaSources()
	{
		$.ajax({
			cache: false,
			url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listMediaSources",
			type: "GET",
			complete: function(jqhxr,status) {},
			error: function(jqxhr, status, errorThrown) {
				alert("AJAX ERROR - check the console!");
				console.error(jqxhr, status, errorThrown);
			},
			success: function(data, status, jqxhr) {		
				for (var x=0; x<data.length; ++x)
				{
					$("#mediaSourceSelector").append(
						$("<option>").val(data[x].mediaSourceID).text(data[x].displayName)
					);
				}
				
			},
		});	
	}
	
})();
