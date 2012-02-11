/**
	Holds the JS used for the player system, playlist management etc
*/
(function(){
	var apikey='{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}';
	var initialProgressEvent=false;	//used to ensure that the initial progress event is the only one handled
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
			progress: function(event) {	
				//if this is the first time the progress event has been handled				
				if(!initialProgressEvent)
				{
 					$("#jquery_jplayer_1").jPlayer("play");
 					initialProgressEvent=true;
 				}
			},
			swfPath: "./js/jQuery.jPlayer.2.1.0/",
			supplied: "mp3,flv",
			preload: "none",
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
		
		$("#searchForm").submit(function(event){
			event.preventDefault();
			event.stopPropagation();
			
			searchForMedia(	
				$("#search_mediaSourceSelector").val(), 
				$("#search_dir").val(), 
				$("#search_query").val()
			);			
		});
		
		/**
			Add handlers for buttons
		**/
		//initialise the click handler for config
		$("#configButton_txt").click(displayConfig);
		
		//init for search functionality
		$("#searchButton_txt").click(function(){
			$("#searchContainer").slideDown("fast",function(){
				$("#search_query").focus();
			});
			
		});

		//addSelectedToPlaylist handling
		$("#addSelectedToPlaylist").click(function(){
			$("#tracklist input[type='checkbox'][name='trackCheckbox']:checked").siblings("a.addToPlaylistButton").click();
		});
		
		//logout button handling
		$("#logoutButton").click(function(){

	        $.ajax({
	            cache: false,
	            url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=logout&apikey="+apikey,
   		        type: "GET",
            	complete: function(jqxhr, status) {},
            	error: function(jqxhr, status, errorThrown) {
            	    alert("AJAX ERROR - check the console!");
            	    console.error(jqxhr, status, errorThrown);
            	},
            	success: function(data, status, jqxhr) {
					$("<div id='logoutNotification'>Successfully Logged Out</div>").appendTo($("body")).dialog({
						modal: true,
						closeOnEscape: false,
						draggable: false,
						resizable: false,
						dialogClass: "loggedout",
						height: 60,
						width: 180,
						title: "Information",
					});
				},
			});
			return false;
		});		
		
		getMediaSources();
		//load the nowPlaying from localStorage
		loadNowPlaying();
		
		//load jPlayer Inspector
	//	$("#jPlayerInspector").jPlayerInspector({jPlayer:$("#jquery_jplayer_1")});
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
		Add a track to the list of playable files (centre container)
	*/
	function addTrackToFileList(file, folderName, mediaSourceID)
	{
		var className = (file.streamers.length == 0)?"unplayable":"playable";
		
		$("<li></li>").append(
				$("<input type='checkbox' name='trackCheckbox'/>")
			).append(
				$("<a href='javascript:;' class='addToPlaylistButton'>+</a>")
			).append(
				$("<a href='javascript:;' class='downloadButton'>D</a>")
			).append(
				"|"
			).append(
				$("<span></span>")
					.text(file.displayName)
					.addClass("trackName")
					.attr("data-dir", folderName+"/")
					.attr("data-filename", file.filename)					
					.attr("data-streamers", JSON.stringify(file.streamers))
					.attr("data-media_source", mediaSourceID)
			)
			.addClass(className)
			.appendTo($("#tracklist"));
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
		
		if(mediaType=="a")
		{
			initialProgressEvent=true;
			$("#jquery_jplayer_1").jPlayer( "setMedia", mediaObject).jPlayer("play");
		}
		else
		{
			//play event is actually handled by the progress event
			initialProgressEvent=false;
			$("#jquery_jplayer_1").jPlayer( "setMedia", mediaObject ).jPlayer("load");
		}
		
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
		//updated to include sub-directory browsing, now slower than before as it
		// is forced to recurse through subdirectories etc, 
		// unbind is now required as the tree structure is now no longer entirely 
		// replaced with the new one
		$("#folderlist li a").unbind('click').click(function(){	
			$("#folderlist .currentlySelected").removeClass("currentlySelected");
			$(this).parent().addClass("currentlySelected");
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
		var folderName = "", appendTarget;
		
		if(clickedObj)	// if it's a subfolder, else it's the root
		{
			folderName = $(clickedObj).attr("data-parent")+""+$(clickedObj).text();
			
			appendTarget = $(clickedObj).siblings("ul.subdir");
			if(appendTarget.length==0)
			{
				appendTarget = $("<ul class='subdir'></ul>");
				$(clickedObj).parent().append(appendTarget);
			}
		}
		else
		{
			appendTarget = $("#folderlist");
		}		
		
		//display loading placeholder
		displayLoading();
		
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
				
				//remove loading placeholder
				$("#tracklist").empty();
				$(appendTarget).empty();
				
				$("#tracklistHeader").text($("#mediaSourceSelector option:selected").text()+""+(folderName==""?"/":folderName));
				
				for (dir in data.Directories)
				{
					$("<li></li>").append(
						$("<a href='javascript:;'></a>")
							.text(data.Directories[dir])
							.attr("data-parent", data.CurrentPath)
							.attr("data-media_source", mediaSourceID)
					)
					.appendTo(appendTarget);
				}
				addFolderClickHandlers();

				//data.Files
				for (file in data.Files)
				{	
					addTrackToFileList(data.Files[file], folderName, mediaSourceID);
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
			complete: function(jqxhr,status) {},
			error: function(jqxhr, status, errorThrown) {
				
				//if not logged in, display the login form
				if(jqxhr.status==401)
					doLogin();
			},
			success: function(data, status, jqxhr) {		
				$("#mediaSourceSelector, #search_mediaSourceSelector").empty();
				$("#search_mediaSourceSelector").append("<option value='all'>All</option>");
				for (var x=0; x<data.length; ++x)
				{
					$("#mediaSourceSelector, #search_mediaSourceSelector").append(
						$("<option>").val(data[x].mediaSourceID).text(data[x].displayName)
					);
				}
				$("#mediaSourceSelector").change();
			},
		});	
	}
	
	function displayLoading()
	{
		$("#tracklist").empty().append();
		$("#tracklistHeader").html("Loading..."+"<img src='img/ajax.gif' alt='loading' class='loading' />");
	}	
	
	/**
		Search the backend for media
	*/
	function searchForMedia(mediaSourceID,dir,query)
	{
		displayLoading();
		$.ajax({
			cache: false,
			url: g_ultrasonic_basePath+"/backend/rest.php?apikey="+apikey+"&action=search&mediaSourceID="+mediaSourceID+"&query="+query+"&dir="+dir,
			type: "GET",
			complete: function(jqxhr,status) {},
			error: function(jqxhr, status, errorThrown) {
				alert("AJAX Error - check the console");
				console.error(jqxhr, status, errorThrown);
			},
			success: function(data, status, jqxhr) {	
			
				$("#tracklist").empty();
				
				$("#tracklistHeader").text("Search Results Within "+$("#search_mediaSourceSelector option:selected").text()+" For: "+query);
				
				for (var x=0; x<data.length; ++x)
				{
					//data[x].mediaSourceID
					//data[x].results.dirs
					for (var fx=0; fx<data[x].results.files.length; ++fx)
					{
						addTrackToFileList(data[x].results.files[fx].fileObject, data[x].results.files[fx].path, data[x].mediaSourceID);
					}
				}
				addTrackClickHandlers();
				
				//reset the search bar
				$("#searchContainer").slideUp();
				$("#search_mediaSourceSelector option[value='all']").attr("selected","selected");
				$("#search_query").val("");
			},
		});	
	}
	
	/**
		display and handle the login form if required
	*/
	function doLogin()
	{
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
						},
						error: function(jqhxr,textstatus,errorthrown){
							console.debug(jqhxr,textstatus,errorthrown);
							alert("Login Failed");							
						}
					});
				}
			}
		});
	}
	
	
	/******************************************************************
		Configuration Functions
	*******************************************************************/
	function displayConfig(event)
	{
		console.log(this);
		//generate a dialog and display it
		if($("#configDialog").length==0)
			$("<div id='configDialog' />")
				.append(
					$("<ul id='configTabs'/>")
						.append($("<li><a href='#tab_client'>Client</a></li>"))
						.append($("<li><a href='#tab_server'>Server</a></li>"))
				)
				.append(
					$("<div id='tab_client'></div>")
						.append($("<fieldset><legend>OptionGroup</legend>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								</fieldset>"))
						.append($("<fieldset><legend>OptionGroup</legend>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								</fieldset>"))
						.append($("<fieldset><legend>OptionGroup</legend>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								</fieldset>"))
				)
				.append(
					$("<div id='tab_server'></div>")
						.append($("<fieldset><legend>Settings!</legend>\
							<p><label>Server Option</label><input type='text' /></p>\
							<p><label>Server Option</label><input type='text' /></p>\
						</fieldset>"))
				)
				.appendTo("body");
		
		$("#configDialog").dialog({
			autoOpen: true,
			modal: true,
			title: 'Ultrasonic Configuration - Mockup',
			width: "500px",
			buttons: {
				'Save' : function(){
					console.log("AJAX Save the settings!");
					$( this ).dialog( "close" );
				},
				Cancel: function(){
					$( this ).dialog( "close" );
				}
			}
		}).tabs({
			selected: 0
		});
	}
	
})();
