/**
	Holds the JS used for the player system, playlist management etc
*/
(function(){
	var apikey='{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}';
	/**
		jQuery Entry Point
	*/
	$(document).ready(function(){
	
		/** Hide the video pane **/
		showHideVideoPane("a");
	
		/** Setup the jPlayer */
		$("#jquery_jplayer_1").jPlayer({
			ready: function () {
				$(this).jPlayer("setMedia", {
					mp3: "",
					flv: ""
				});
			},
			swfPath: "./js/jQuery.jPlayer.2.1.0/",
			supplied: "mp3,flv",
			wmode: "window"
		}).bind($.jPlayer.event.ended, function(event){

		//get the currently played one
			var playingObject = $("#playlistTracks .jPlaying");
			playingObject.removeClass("jPlaying");
			
			var nextObjectSpan = playingObject.parent().next("li").children("a.playNow");
			if(!nextObjectSpan)
				return;
			
			play_jPlayerTrack(nextObjectSpan);
		});
		
		$("#jp_container_1 ul.jp-controls .jp-play").click(function(){
			//if there is no track being played then play the first
			if($("#playlistTracks .jPlaying").length == 0)
			{
				$("#playlistTracks li:first a.playNow").click();
			}
		});

		//add an additional handler onto the stop buttn
		$("#jp_container_1 ul.jp-controls .jp-stop").click(function(){
			//reset the media if it's a video
		});
		
		$("#mediaSourceSelector").change(function(event){
			updateFolderBrowser($("#mediaSourceSelector").val());
		});
		
		//present the login form:
		
		$("#loginFormContainer").dialog({
			autoOpen: true,
			modal: true,
			title: 'Login',
			buttons: {
				'Login': function(){
					var hash = new jsSHA($("#passwordInput").val()).getHash("SHA-256","B64");
					$.ajax({
						url:'backend/rest.php?action=login&apikey='+apikey,
						type: 'POST',
						data: {
							'username': $("#username").val(),
							'password': hash
						},
						success: function(){
							$("#loginFormContainer").dialog("close");
							getMediaSources();
							
							//load the nowPlaying from localStorage
							loadNowPlaying();
							
						},
						error: function(jqhxr,textstatus,errorthrown){
							console.debug(jqhxr,textstatus,errorthrown);
							alert("Login Failed");							
						}
					});
				}
			}
		});

	});

	/**
		Load the now playing list from HTML5 LocalStorage
	*/
	function loadNowPlaying()
	{
		var nowPlaying = localStorage.getItem("nowPlaying");
		
		if(typeof nowPlaying === "undefined" || !nowPlaying)
			return;
		
		var trackList = $.parseJSON(nowPlaying);
		for(var x=0; x<trackList.length; ++x)
		{
			addToNowPlaying(trackList[x]);
		}
		addNowPlayingClickHandlers();
	}
	
	
	/**
		Saves the now playing list to HTML5 LocalStorage
	*/
	function saveNowPlaying()
	{
		var trackList = $("#playlistTracks li a.playNow"),
			nowPlaying = [];
		
		for(var x=0; x<trackList.length; ++x)
		{
			nowPlaying.push({
				'text': $(trackList[x]).text(),
				'filename': $(trackList[x]).attr("data-filename"),
				'dir': $(trackList[x]).attr("data-dir"),
				'streamers':$(trackList[x]).attr("data-streamers"),
				'media_source':$(trackList[x]).attr("data-media_source"),
			});
		}
		
		localStorage.setItem("nowPlaying", JSON.stringify(nowPlaying));
	}
	
	/**
		Add a track to the now playing list
	*/
	function addToNowPlaying(trackObject)
	{	
		$("#playlistTracks").append(		
			$("<li></li>").append(
				$("<a href='javascript:;' class='removeFromPlaylist'>R</a>")
			).append(
				"|"
			).append(
				$("<a href='javascript:;' class='playNow'></a>")
					.text( trackObject.text )
					.attr( "data-filename", trackObject.filename )
					.attr( "data-dir", trackObject.dir )
					.attr( "data-streamers", trackObject.streamers )
					.attr( "data-media_source", trackObject.media_source )
			)
		);
	}
	
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
		
		var streamerObject = $.parseJSON(remote_streamers), mediaObject = {}, mediaType="a";
		for(var x=0; x<streamerObject.length; ++x)
		{
			mediaObject[streamerObject[x].extension] = g_ultrasonic_basePath+"/backend/rest.php"+"?action=getStream"+
														"&filename="+encodeURIComponent(remote_filename)+
														"&dir="+encodeURIComponent(remote_directory)+
														"&mediaSourceID="+encodeURIComponent(remote_mediaSource)+
														"&streamerID="+streamerObject[x].streamerID+
														"&apikey="+apikey;
			mediaType = streamerObject[x].mediaType=="v"?"v":"a";
		}
		
		showHideVideoPane(mediaType);
		
		$("#jquery_jplayer_1").jPlayer( "setMedia", mediaObject).jPlayer("play");
	}
	
	/**
		Shows or hides the videopane depending on the passed mediaType (a/v)
	*/
	function showHideVideoPane(mediaType)
	{
			//is only based on the last mediaType:
		if(mediaType=="v")
		{
			$("#centerPlayerContainer").height("auto");
			$("#centerTrackContainer").css({ 'bottom': $("#centerPlayerContainer").height() });
		}
		else
		{
			$("#centerPlayerContainer").height(3);
			$("#centerTrackContainer").css({ 'bottom':"3px"});
		}

	}
	
	/**
		Adds the handlers to the track list in the "album view"
	*/
	function addTrackClickHandlers()
	{

		var parentElement = $("#tracklist").children("li");
		parentElement.children("a.downloadButton").click(function(){
			
			var trackObject = $(this).parent().children("span.trackName");
			var     remote_filename = $(trackObject).attr("data-filename"),
			        remote_directory = $(trackObject).attr("data-dir"),
			        remote_streamers = $(trackObject).attr("data-streamers"),
			        remote_mediaSource = $(trackObject).attr("data-media_source");
			                                                 

			var url = g_ultrasonic_basePath+"/backend/rest.php"+"?action=downloadFile"+
					"&filename="+encodeURIComponent(remote_filename)+
			    	"&dir="+encodeURIComponent(remote_directory)+
		    		"&mediaSourceID="+encodeURIComponent(remote_mediaSource)+
		    		"&apikey="+apikey;
		    		
			window.open(url);	//open in new window
		});
		
		parentElement.children("a.addToPlaylistButton").click(function(){
			var parentObj = $(this).parent(),
				trackTagObject = parentObj.children("span.trackName");
			
			if(parentObj.hasClass("unplayable"))
				return false;
			
			trackObject = {
				'text': $(trackTagObject).text(),
				'filename': $(trackTagObject).attr("data-filename"),
				'dir': $(trackTagObject).attr("data-dir"),
				'streamers': $(trackTagObject).attr("data-streamers"),
				'media_source': $(trackTagObject).attr("data-media_source")
			}
			
			addToNowPlaying(trackObject);
			saveNowPlaying();
			addNowPlayingClickHandlers();
		});
		
		$( "#tracklist li" ).draggable({
			appendTo: "body",
			helper: function(event) {
				return $("<div class='draggingTrack'></div>");
			}, 
			cancel: ".unplayable",
			cursor: "move",
			cursorAt: {
				top: 16,
				left: 16
			}
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
							
				trackObject = {
					'text': $(trackTagObject).text(),
					'filename': $(trackTagObject).attr("data-filename"),
					'dir': $(trackTagObject).attr("data-dir"),
					'streamers': $(trackTagObject).attr("data-streamers"),
					'media_source': $(trackTagObject).attr("data-media_source")
				}
				
				addToNowPlaying(trackObject);
				saveNowPlaying();
				addNowPlayingClickHandlers();
			}
		}).sortable({
			items: "li:not(.placeholder)",
			sort: function() {
				// gets added unintentionally by droppable interacting with sortable
				// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
				$( this ).removeClass( "ui-state-default" );
			}
		});
		
		addNowPlayingClickHandlers();
		
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
	function addNowPlayingClickHandlers()
	{
		$("#playlistTracks li a").unbind("click");
		$("#playlistTracks li a.playNow").click(function(){
			play_jPlayerTrack(this);
		});
		$("#playlistTracks li a.removeFromPlaylist").click(function(){
			//play_jPlayerTrack(this);
			$(this).parent().remove();
			saveNowPlaying();
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
			url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listDirContents&apikey="+apikey,
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
				{	
					$("<li></li>").append(
						$("<a href='javascript:;' class='addToPlaylistButton'>+</a>")
					).append(
						$("<a href='javascript:;' class='downloadButton'>D</a>")
					).append(
						"|"
					).append(
						$("<span></span>")
							.text(data.Files[file].displayName)
							.addClass("trackName")
							.attr("data-dir", folderName+"/")
							.attr("data-filename", data.Files[file].filename)					
							.attr("data-streamers", JSON.stringify(data.Files[file].streamers))
							.attr("data-media_source", mediaSourceID)
					)
					.addClass((data.Files[file].streamers.length == 0)?"unplayable":"playable")
					.appendTo($("#tracklist"));
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
			url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listMediaSources&apikey="+apikey,
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